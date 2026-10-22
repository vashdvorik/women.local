<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BroadcastRequest;
use App\Models\Broadcast;
use App\Models\BroadcastDelivery;
use App\Models\SiteSetting;
use App\Services\Broadcasts\BroadcastLauncher;
use App\Services\Broadcasts\TelegramBroadcaster;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

/**
 * «Рассылка» — лагерь «Кабинеты участниц»: сообщение всем (или платным, или бесплатным) участницам в Telegram с
 * кнопками-ссылками и фото, пробная отправка себе, отправка сразу или по расписанию, отчёт «кому дошло».
 * Сама отправка — App\Jobs\SendBroadcast (очередь), запуск и отмена — App\Services\Broadcasts\BroadcastLauncher.
 */
class BroadcastController extends Controller
{
    /** Ключ настройки: Telegram ID для пробной отправки (запоминается, чтобы не вводить каждый раз). */
    public const TEST_CHAT_KEY = 'broadcast_test_chat';

    public function __construct(
        private readonly BroadcastLauncher $launcher,
        private readonly TelegramBroadcaster $telegram,
    ) {}

    public function index(): View
    {
        return view('admin.broadcasts.index', [
            'broadcasts' => Broadcast::query()->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.broadcasts.form', $this->formData(new Broadcast(['audience' => Broadcast::AUDIENCE_APPROVED, 'texts' => [], 'buttons' => []])));
    }

    public function store(BroadcastRequest $request): RedirectResponse
    {
        return $this->persist($request, new Broadcast(['status' => Broadcast::STATUS_DRAFT, 'created_by' => $request->user()?->id]));
    }

    public function edit(Broadcast $broadcast): View|RedirectResponse
    {
        if (! $broadcast->isEditable()) {
            return redirect()->route('admin.broadcasts.show', $broadcast);
        }

        return view('admin.broadcasts.form', $this->formData($broadcast));
    }

    public function update(BroadcastRequest $request, Broadcast $broadcast): RedirectResponse
    {
        if (! $broadcast->isEditable()) {
            return redirect()->route('admin.broadcasts.show', $broadcast)->with('error', __('Эту рассылку уже нельзя менять.'));
        }

        return $this->persist($request, $broadcast);
    }

    public function show(Request $request, Broadcast $broadcast): View
    {
        $filter = $request->string('filter')->toString();

        $deliveries = $broadcast->deliveries()
            ->with('botUser:id,full_name,telegram_username')
            ->when(in_array($filter, [BroadcastDelivery::SENT, BroadcastDelivery::FAILED, BroadcastDelivery::BLOCKED, BroadcastDelivery::PENDING], true),
                fn ($q) => $q->where('status', $filter))
            // Сначала то, что требует внимания: ошибки и заблокировавшие бота.
            ->orderByRaw("case status when 'failed' then 0 when 'blocked' then 1 when 'pending' then 2 else 3 end")
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.broadcasts.show', [
            'broadcast' => $broadcast,
            'deliveries' => $deliveries,
            'filter' => $filter,
            'audienceCount' => $broadcast->isActive() && $broadcast->total === 0 ? $broadcast->recipients()->count() : $broadcast->total,
        ]);
    }

    public function cancel(Broadcast $broadcast): RedirectResponse
    {
        $this->launcher->cancel($broadcast);

        return redirect()->route('admin.broadcasts.show', $broadcast)->with('success', __('Рассылка отменена. Тем, кому сообщение уже отправлено, его не вернуть.'));
    }

    public function destroy(Broadcast $broadcast): RedirectResponse
    {
        if ($broadcast->status === Broadcast::STATUS_SENDING) {
            return redirect()->route('admin.broadcasts.show', $broadcast)->with('error', __('Рассылка идёт. Сначала отмените её.'));
        }

        if ($broadcast->hasPhoto()) {
            Storage::disk('originals')->delete($broadcast->photo_path);
        }
        $broadcast->delete();

        return redirect()->route('admin.broadcasts.index')->with('success', __('Рассылка удалена.'));
    }

    // ------------------------------------------------------------------ сохранение и отправка

    private function persist(BroadcastRequest $request, Broadcast $broadcast): RedirectResponse
    {
        $action = $request->string('action')->toString();

        $broadcast->fill([
            'texts' => $request->cleanTexts(),
            'buttons' => $request->cleanButtons() ?: null,
            'audience' => $request->string('audience')->toString(),
        ]);
        $this->applyPhoto($request, $broadcast);

        if ($action === 'send' && $request->string('send_mode')->toString() === 'later') {
            $broadcast->fill(['status' => Broadcast::STATUS_SCHEDULED, 'scheduled_at' => $request->scheduledAtUtc()]);
        } elseif ($broadcast->status === Broadcast::STATUS_SCHEDULED && $action !== 'send') {
            // Правят запланированную рассылку (или шлют пробу): время остаётся, если его не меняли.
            $broadcast->fill(['scheduled_at' => $request->scheduledAtUtc() ?? $broadcast->scheduled_at]);
        } else {
            $broadcast->fill(['status' => $broadcast->exists ? $broadcast->status : Broadcast::STATUS_DRAFT, 'scheduled_at' => null]);
        }

        $broadcast->save();

        return match ($action) {
            'test' => $this->sendTest($request, $broadcast),
            'send' => $this->send($request, $broadcast),
            default => redirect()->route('admin.broadcasts.edit', $broadcast)->with('success', __('Черновик сохранён.')),
        };
    }

    private function send(BroadcastRequest $request, Broadcast $broadcast): RedirectResponse
    {
        if ($request->string('send_mode')->toString() === 'later') {
            return redirect()->route('admin.broadcasts.show', $broadcast)
                ->with('success', __('Рассылка запланирована на :time (по Кишинёву).', ['time' => $broadcast->scheduledLocal()->format('d.m.Y H:i')]));
        }

        if ($broadcast->recipients()->count() === 0) {
            return redirect()->route('admin.broadcasts.edit', $broadcast)->with('error', __('В выбранной группе нет участниц: отправлять некому.'));
        }

        // Запланированная рассылка, которую решили отправить сразу, тоже стартует отсюда.
        $broadcast->forceFill(['status' => Broadcast::STATUS_DRAFT])->save();
        $this->launcher->start($broadcast);

        return redirect()->route('admin.broadcasts.show', $broadcast)->with('success', __('Рассылка запущена. Отчёт обновляется по мере отправки.'));
    }

    /** Пробная отправка: то же сообщение, по одному на каждый заполненный язык, на указанный Telegram ID. */
    private function sendTest(BroadcastRequest $request, Broadcast $broadcast): RedirectResponse
    {
        $chat = $request->string('test_chat')->toString();
        SiteSetting::write(self::TEST_CHAT_KEY, $chat);

        $errors = [];
        foreach ($broadcast->filledLocales() as $locale) {
            $outcome = $this->telegram->send($broadcast, $chat, $locale);

            if (! $outcome->isSent()) {
                $errors[] = strtoupper($locale).': '.($outcome->error ?? __('Telegram просит подождать, попробуйте ещё раз.'));
            }
        }

        $redirect = redirect()->route('admin.broadcasts.edit', $broadcast);

        return $errors === []
            ? $redirect->with('success', __('Пробное сообщение отправлено (языков: :count). Проверьте Telegram.', ['count' => count($broadcast->filledLocales())]))
            : $redirect->with('error', __('Пробное сообщение не дошло: :errors. Telegram ID должен принадлежать тому, кто хотя бы раз написал боту.', ['errors' => implode('; ', $errors)]));
    }

    private function applyPhoto(BroadcastRequest $request, Broadcast $broadcast): void
    {
        if ($request->hasFile('photo')) {
            $this->deletePhoto($broadcast);
            $broadcast->photo_path = $this->storePhoto($request->file('photo'));
            $broadcast->photo_file_id = null;

            return;
        }

        if ($request->boolean('remove_photo')) {
            $this->deletePhoto($broadcast);
            $broadcast->photo_path = null;
            $broadcast->photo_file_id = null;
        }
    }

    /** Фото целиком (без обрезки), не крупнее 1600 px по длинной стороне, JPEG: так его принимает Telegram. */
    private function storePhoto(UploadedFile $file): string
    {
        $image = ImageManager::usingDriver(new Driver())->decode($file->getRealPath())->scaleDown(width: 1600, height: 1600);
        $path = 'broadcasts/'.now()->format('Y/m').'/'.Str::lower(Str::random(16)).'.jpg';
        Storage::disk('originals')->put($path, (string) $image->encode(new JpegEncoder(quality: 85)));

        return $path;
    }

    private function deletePhoto(Broadcast $broadcast): void
    {
        if ($broadcast->hasPhoto()) {
            Storage::disk('originals')->delete($broadcast->photo_path);
        }
    }

    /** @return array<string, mixed> */
    private function formData(Broadcast $broadcast): array
    {
        return [
            'broadcast' => $broadcast,
            'counts' => collect(Broadcast::AUDIENCES)->mapWithKeys(fn (string $audience) => [$audience => Broadcast::audienceQuery($audience)->count()])->all(),
            'testChat' => SiteSetting::read(self::TEST_CHAT_KEY, ''),
        ];
    }
}
