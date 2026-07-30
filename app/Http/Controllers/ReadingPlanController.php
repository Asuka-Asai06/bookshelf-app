<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\ReadingPlan\IndexReadingPlanRequest;
use App\Http\Requests\ReadingPlan\StoreReadingPlanRequest;
use App\Http\Requests\ReadingPlan\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画一覧を表示する。
     *
     * @param  IndexReadingPlanRequest  $request  読書状態で絞り込むリクエスト
     * @return View 読書計画一覧画面
     */
    public function index(IndexReadingPlanRequest $request): View
    {
        $validated = $request->validated();

        $readingPlans = ReadingPlan::query()
            ->with('book')
            ->whereBelongsTo(auth()->user())
            ->status($validated['status'] ?? null)
            ->orderBy('target_date')
            ->get();

        return view('reading-plans.index', [
            'readingPlans' => $readingPlans,
            'currentStatus' => $validated['status'] ?? '',
        ]);
    }

    /**
     * 読書計画登録画面を表示する。
     *
     * @return View 読書計画登録画面
     */
    public function create(): View
    {
        $books = Book::whereDoesntHave('readingPlans', function ($query) {
            $query->where('user_id', auth()->id());
        })->orderBy('author')
            ->get();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 読書計画を登録する。
     *
     * @param  StoreReadingPlanRequest  $request  読書計画登録用のリクエスト
     * @return RedirectResponse 読書計画一覧へリダイレクト
     */
    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            ReadingPlan::create([
                'user_id' => auth()->id(),
                'book_id' => $validated['book_id'],
                'target_date' => $validated['target_date'],
            ]);
        });

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を登録しました。');
    }

    /**
     * 読書計画更新画面を表示
     *
     * @param  ReadingPlan  $readingPlan  更新対象の読書計画
     * @return View 読書計画編集画面
     */
    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * 読書計画を更新する。
     *
     * @param  UpdateReadingPlanRequest  $request  読書計画更新用のリクエスト
     * @return RedirectResponse 読書計画一覧へリダイレクト
     */
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update($request->validated());

        return redirect()->route('reading-plans.index', $readingPlan)
            ->with('success', '読書計画を更新しました。');
    }

    /**
     * 読書計画を削除する。
     *
     * @param  ReadingPlan  $readingPlan  削除対象の読書計画
     * @return RedirectResponse 読書計画一覧へリダイレクト
     */
    public function destroy(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('delete', $readingPlan);

        DB::transaction(function () use ($readingPlan) {
            $readingPlan->delete();
        });

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }

    /**
     * 読了済みに切り替える。
     *
     * @param  ReadingPlan  $readingPlan  読了対象の読書計画
     * @return RedirectResponse 読書計画一覧へリダイレクト
     */
    public function complete(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を完了しました。');
    }
}
