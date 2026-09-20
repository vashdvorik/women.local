<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();

        $subscribers = Subscriber::query()
            ->when($search !== '', fn ($q) => $q
                ->where('email', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.subscribers.index', compact('subscribers', 'search'));
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return redirect()->route('admin.subscribers.index')->with('success', 'Подписчик удалён.');
    }

    /** Экспорт для программы рассылки: почта; имя; дата. UTF-8 с BOM — Excel открывает без плясок. */
    public function exportCsv(): StreamedResponse
    {
        $name = 'subscribers-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['email', 'name', 'date'], ';');

            Subscriber::query()->latest()->chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $subscriber) {
                    fputcsv($out, [
                        $subscriber->email,
                        $subscriber->name ?? '',
                        $subscriber->created_at->format('d.m.Y'),
                    ], ';');
                }
            });

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Плоский список почт — по одной в строке, для поля «добавить получателей». */
    public function exportTxt(): StreamedResponse
    {
        $name = 'subscribers-'.now()->format('Y-m-d').'.txt';

        return response()->streamDownload(function () {
            Subscriber::query()->latest()->chunk(500, function ($chunk) {
                foreach ($chunk as $subscriber) {
                    echo $subscriber->email."\n";
                }
            });
        }, $name, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
