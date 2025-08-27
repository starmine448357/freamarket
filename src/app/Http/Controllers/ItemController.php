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
    /**
     * おすすめ一覧（初期表示）
     * - 最新順で12件ずつ表示
     * - ビューでは $tab='recommend' を使ってタブの見た目を切替
     */
// おすすめ（/）
    public function index()
    {
        $items = Item::with(['categories','purchase'])
            ->latest()
            ->paginate(12);

        return view('items.index', [
            'items' => $items,
            'tab'   => 'recommend',
        ]);
    }

    // マイリスト（/mylist）
    public function mylist()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $items = Item::with(['categories','purchase'])
            ->whereHas('likes', fn($q) => $q->where('user_id', Auth::id()))
            // ↓自分の商品を除外したいなら残す。不要なら削除
            ->where('user_id', '!=', Auth::id())
            ->latest()
            ->paginate(12);

        return view('items.index', [
            'items' => $items,
            'tab'   => 'mylist',
        ]);
    }
    /**
     * 商品詳細
     * - いいね済みかどうかを $liked でビューへ渡す
     */
    public function show(Item $item)
    {
        $item->load(['user', 'categories', 'comments.user', 'likes']);

        $liked = Auth::check()
            ? $item->likes()->where('user_id', Auth::id())->exists()
            : false;

        return view('items.show', compact('item', 'liked'));
    }

    /**
     * 出品フォーム
     */
    public function create()
        {
            $names = [
                'ファッション','家電','インテリア','レディース','メンズ','コスメ',
                '本','ゲーム','スポーツ','キッチン','ハンドメイド','アクセサリー',
                'おもちゃ','ベビー・キッズ',
            ];

            $categories = Category::select('id','name')
                ->whereIn('name', $names)
                ->orderByRaw('FIELD(name, "ファッション","家電","インテリア","レディース","メンズ","コスメ","本","ゲーム","スポーツ","キッチン","ハンドメイド","アクセサリー","おもちゃ","ベビー・キッズ")')
                ->get();

            return view('items.create', compact('categories'));
        }    /**
     * 出品登録
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'brand'        => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'price'        => ['required', 'integer', 'min:0', 'max:9999999'],
            'condition'    => ['required', Rule::in(['new', 'like_new', 'used'])],
            'status'       => ['nullable', Rule::in(['selling', 'sold'])],
            'image'        => ['nullable', 'image', 'mimes:jpeg,png', 'max:4096'],
            'categories'   => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ]);

        // 画像アップロード（任意）
        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('items', 'public');
        }

        // 商品作成
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

        // カテゴリ紐づけ
        if (!empty($validated['categories'])) {
            $item->categories()->sync($validated['categories']);
        }

        return redirect()
            ->route('items.show', $item)
            ->with('success', '商品を登録しました。');
    }

    /**
     * 編集フォーム
     */
    public function edit(Item $item)
    {
        // ポリシーを導入している想定（未導入ならコメントアウトでもOK）
        $this->authorize('update', $item);

        $categories = Category::orderBy('name')->get();
        $selected   = $item->categories()->pluck('categories.id')->all();

        return view('items.edit', compact('item', 'categories', 'selected'));
    }

    /**
     * 更新
     */
    public function update(Request $request, Item $item)
    {
        $this->authorize('update', $item);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'brand'        => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'price'        => ['required', 'integer', 'min:0', 'max:9999999'],
            'condition'    => ['required', Rule::in(['new', 'like_new', 'used'])],
            'status'       => ['required', Rule::in(['selling', 'sold'])],
            'image'        => ['nullable', 'image', 'mimes:jpeg,png', 'max:4096'],
            'categories'   => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ]);

        // 画像差し替え（古い画像があれば削除）
        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $item->image_path = $request->file('image')->store('items', 'public');
        }

        // 基本情報更新
        $item->fill([
            'title'       => $validated['title'],
            'brand'       => $validated['brand'] ?? null,
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'],
            'condition'   => $validated['condition'],
            'status'      => $validated['status'],
        ])->save();

        // カテゴリ更新
        $item->categories()->sync($validated['categories'] ?? []);

        return redirect()
            ->route('items.show', $item)
            ->with('success', '商品を更新しました。');
    }

    /**
     * 削除
     */
    public function destroy(Item $item)
    {
        $this->authorize('delete', $item);

        // 画像削除
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        // 中間テーブル解除 → 本体削除
        $item->categories()->detach();
        $item->delete();

        return redirect()
            ->route('items.index')
            ->with('success', '商品を削除しました。');
    }
}
