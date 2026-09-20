<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesFlatCards;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExpertRequest;
use App\Models\Expert;
use App\Support\Blocks;
use App\Support\CardTone;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpertController extends Controller
{
    use ManagesFlatCards;

    private const TEXT_FIELDS = ['name', 'role', 'specialization', 'description', 'looking_for', 'can_offer'];

    public function index(): View
    {
        $experts = Expert::with('translations')->ordered()->get();

        return view('admin.experts.index', compact('experts'));
    }

    public function create(): View
    {
        return view('admin.experts.create', ['expert' => new Expert(['tone' => CardTone::DEFAULT])]);
    }

    public function store(ExpertRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $expert = Expert::create([
                'photo_path' => Blocks::normalizePath($request->validated('photo_path')),
                'tone' => $request->validated('tone'),
                'is_published' => $request->boolean('is_published'),
                'position' => $this->nextPosition(Expert::class),
            ]);

            $this->syncFlatTranslations($expert, $request->validated('translations', []), self::TEXT_FIELDS, ['tags']);
        });

        return redirect()->route('admin.experts.index')->with('success', 'Эксперт добавлен.');
    }

    public function edit(Expert $expert): View
    {
        $expert->load('translations');

        return view('admin.experts.edit', compact('expert'));
    }

    public function update(ExpertRequest $request, Expert $expert): RedirectResponse
    {
        DB::transaction(function () use ($request, $expert) {
            $expert->update([
                'photo_path' => Blocks::normalizePath($request->validated('photo_path')),
                'tone' => $request->validated('tone'),
                'is_published' => $request->boolean('is_published'),
            ]);

            $this->syncFlatTranslations($expert, $request->validated('translations', []), self::TEXT_FIELDS, ['tags']);
        });

        return redirect()->route('admin.experts.index')->with('success', 'Эксперт сохранён.');
    }

    public function destroy(Expert $expert): RedirectResponse
    {
        $expert->delete();

        return redirect()->route('admin.experts.index')->with('success', 'Эксперт удалён.');
    }

    public function move(Request $request, Expert $expert): RedirectResponse
    {
        $this->moveByPosition($expert, $request->string('direction')->toString());

        return redirect()->route('admin.experts.index');
    }
}
