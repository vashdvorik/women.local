<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManagerMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * «Вопросы AI-менеджеру»: что участницы спрашивают и на что менеджер не нашёл ответа. По таким вопросам админ дописывает
 * знания менеджера или отвечает участнице сам; разобранный вопрос отмечается и пропадает из счётчика.
 */
class ManagerQuestionController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter') === 'all' ? 'all' : 'open';

        $query = ManagerMessage::query()->with('botUser:id,full_name,email,phone')->where('role', ManagerMessage::ROLE_ASSISTANT);

        if ($filter === 'open') {
            $query->where('answered', false)->whereNull('handled_at');
        }

        return view('admin.manager-questions.index', [
            'filter' => $filter,
            'questions' => $query->latest('id')->paginate(25)->withQueryString(),
            'counts' => [
                'open' => ManagerMessage::unanswered()->count(),
                'all' => ManagerMessage::where('role', ManagerMessage::ROLE_ASSISTANT)->count(),
            ],
        ]);
    }

    public function handled(ManagerMessage $message): RedirectResponse
    {
        abort_unless($message->role === ManagerMessage::ROLE_ASSISTANT, 404);

        $message->forceFill(['handled_at' => now()])->save();

        return back()->with('success', __('Вопрос отмечен как разобранный.'));
    }
}
