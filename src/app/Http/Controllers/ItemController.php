<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    // 一覧
    public function index()
    {
        $tab = request('tab', 'recommend'); // デフォルトはおすすめ

        if ($tab === 'mylist') {
            if (!Auth::check()) {
                $items = collect();
                return view('items.index', compact('items', 'tab'));
            }

            $items = Item::with(['categories','purchase'])
                ->whereHas('likes', fn($q) => $q->where('user_id', Auth::id()))
                ->where('user_id', '!=', Auth::id())
                ->latest()
                ->paginate(12);

            return view('items.index', compact('items', 'tab'));
        }

        // おすすめ
        $items = Item::with(['categories','purchase'])->latest()->paginate(12);
        return view('items.index', compact('items', 'tab'));
    }
    // 詳細
    public function show(Item $item)
    {
        $item->load(['user', 'categories', 'comments.user', 'likes']);
        $liked = Auth::check()
            ? $item->likes()->where('user_id', Auth::id())->exists()
            : false;

        return view('items.show', compact('item', 'liked'));
    }

    // 作成フォーム
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('items.create', compact('categories'));
    }

    // 登録
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required','string','max:255'],
            'brand'       => ['nullable','string','max:255'],
            'description' => ['nullable','string'],
            'price'       => ['required','integer','min:0','max:9999999'],
            'condition'   => ['required', Rule::in(['new','like_new','used'])],
            'status'      => ['nullable', Rule::in(['selling','sold'])],
            'image'       => ['nullable','image','mimes:jpeg,png','max:4096'],
            'categories'  => ['nullable','array'],
            'categories.*'=> ['integer','exists:categories,id'],
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('items', 'public');
        }

        $item = Item::create([
            'user_id'     => Auth::id(),
            'title'       => $validated['title'],
            'brand'       => $validated['brand'] ?? null,
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'],
            'condition'   => $validated['condition'],
            'image_path'  => $path,
            'status'      => $validated['status'] ?? 'selling',
        ]);

        if (!empty($validated['categories'])) {
            $item->categories()->sync($validated['categories']);
        }

        return redirect()->route('items.show', $item)->with('success', '商品を登録しました。');
    }

    // 編集フォーム
    public function edit(Item $item)
    {
        $this->authorize('update', $item); // ポリシー導入予定なら
        $categories = Category::orderBy('name')->get();
        $selected = $item->categories()->pluck('categories.id')->all();
        return view('items.edit', compact('item', 'categories', 'selected'));
    }

    // 更新
    public function update(Request $request, Item $item)
    {
        $this->authorize('update', $item);

        $validated = $request->validate([
            'title'       => ['required','string','max:255'],
            'brand'       => ['nullable','string','max:255'],
            'description' => ['nullable','string'],
            'price'       => ['required','integer','min:0','max:9999999'],
            'condition'   => ['required', Rule::in(['new','like_new','used'])],
            'status'      => ['required', Rule::in(['selling','sold'])],
            'image'       => ['nullable','image','mimes:jpeg,png','max:4096'],
            'categories'  => ['nullable','array'],
            'categories.*'=> ['integer','exists:categories,id'],
        ]);

        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $item->image_path = $request->file('image')->store('items', 'public');
        }

        $item->fill([
            'title'       => $validated['title'],
            'brand'       => $validated['brand'] ?? null,
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'],
            'condition'   => $validated['condition'],
            'status'      => $validated['status'],
        ])->save();

        $item->categories()->sync($validated['categories'] ?? []);

        return redirect()->route('items.show', $item)->with('success', '商品を更新しました。');
    }

    // 削除
    public function destroy(Item $item)
    {
        $this->authorize('delete', $item);

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }
        $item->categories()->detach();
        $item->delete();

        return redirect()->route('items.index')->with('success', '商品を削除しました。');
    }
}
