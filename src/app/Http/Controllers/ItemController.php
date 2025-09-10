<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ExhibitionRequest;
use Illuminate\Database\Eloquent\Builder;

class ItemController extends Controller
{
    /** 共通フィルタ適用（商品名のみ検索） */
    private function applyFilters(Builder $items, Request $request): Builder
    {
        $q         = trim((string) $request->input('q', ''));   // キーワード（titleのみ）
        $category  = $request->input('category');               // カテゴリID
        $min       = $request->input('min');                    // 最低価格
        $max       = $request->input('max');                    // 最高価格
        $condition = $request->input('condition');              // new|like_new|used|bad
        $unsold    = $request->boolean('unsold');               // 在庫のみ

        if ($q !== '') {
            $items->where('title', 'like', "%{$q}%");
        }
        if ($category) {
            $items->whereHas('categories', fn ($cq) => $cq->where('categories.id', $category));
        }
        if ($min !== null && $min !== '') {
            $items->where('price', '>=', (int)$min);
        }
        if ($max !== null && $max !== '') {
            $items->where('price', '<=', (int)$max);
        }
        if ($condition) {
            $items->where('condition', $condition);
        }
        if ($unsold) {
            // status で在庫管理している想定。購入有無で見るなら whereDoesntHave('purchase') に差し替え。
            $items->where('status', 'selling');
        }

        return $items;
    }

    /** 商品一覧（検索対応：商品名のみ） */
    public function index(Request $request)
    {
        $items = Item::query()
            ->with(['categories', 'purchase'])
            ->latest();

        $this->applyFilters($items, $request);

        $items = $items->paginate(12)->appends($request->query());

        $categories = Category::select('id', 'name')->orderBy('name')->get();

        return view('items.index', [
            'items'      => $items,
            'tab'        => 'recommend',
            'categories' => $categories,
            'filters'    => $request->only(['q','category','min','max','condition','unsold']),
        ]);
    }

    /** マイリスト（検索条件を保持して適用） */
    public function mylist(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $items = Item::query()
            ->with(['categories', 'purchase'])
            ->whereHas('likes', fn ($q) => $q->where('user_id', Auth::id()))
            ->where('user_id', '!=', Auth::id())   // 自分の出品は除外
            ->latest();

        $this->applyFilters($items, $request);

        $items = $items->paginate(12)->appends($request->query());

        // マイリストでも検索フォームを出すならカテゴリを渡す
        $categories = Category::select('id', 'name')->orderBy('name')->get();

        return view('items.index', [
            'items'      => $items,
            'tab'        => 'mylist',
            'categories' => $categories,
            'filters'    => $request->only(['q','category','min','max','condition','unsold']),
        ]);
    }

    /** 商品詳細 */
    public function show(Item $item)
    {
        $item->load(['user', 'categories', 'comments.user', 'likes'])
             ->loadCount(['likes', 'comments']);

        $liked = Auth::check()
            ? $item->likes()->where('user_id', Auth::id())->exists()
            : false;

        return view('items.show', compact('item', 'liked'));
    }

    /** 出品フォーム */
    public function create()
    {
        $names = [
            'ファッション','家電','インテリア','レディース','メンズ','コスメ',
            '本','ゲーム','スポーツ','キッチン','ハンドメイド','アクセサリー',
            'おもちゃ','ベビー・キッズ',
        ];

        $categories = Category::select('id', 'name')
            ->whereIn('name', $names)
            ->orderByRaw('FIELD(name, "ファッション","家電","インテリア","レディース","メンズ","コスメ","本","ゲーム","スポーツ","キッチン","ハンドメイド","アクセサリー","おもちゃ","ベビー・キッズ")')
            ->get();

        return view('items.create', compact('categories'));
    }

    /** 出品登録 */
    public function store(ExhibitionRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $validated) {
            Storage::disk('public')->makeDirectory('images');

            $path = null;
            $tempName = trim((string) $request->input('temp_image', ''));

            if ($tempName !== '' && Storage::disk('public')->exists("temp/{$tempName}")) {
                if (Storage::disk('public')->move("temp/{$tempName}", "images/{$tempName}")) {
                    $path = "images/{$tempName}";
                    session()->forget('temp_image');
                }
            }
            if ($path === null && $request->hasFile('image')) {
                $path = $request->file('image')->store('images', 'public');
            }
            if ($path === null) {
                return back()->withErrors(['image' => '画像が保存されていません'])->withInput();
            }

            $item = Item::create([
                'user_id'     => Auth::id(),
                'title'       => $validated['title'],
                'brand'       => $validated['brand'] ?? null,
                'description' => $validated['description'],
                'price'       => $validated['price'],
                'condition'   => $validated['condition'],
                'image_path'  => $path,
                'status'      => $validated['status'] ?? 'selling',
            ]);

            $item->categories()->sync($validated['categories']);

            return redirect()->route('items.index');
        });
    }

    /** 編集フォーム */
    public function edit(Item $item)
    {
        $this->authorize('update', $item);

        $categories = Category::orderBy('name')->get();
        $selected   = $item->categories()->pluck('categories.id')->all();

        return view('items.edit', compact('item', 'categories', 'selected'));
    }

    /** 更新 */
    public function update(Request $request, Item $item)
    {
        $this->authorize('update', $item);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'brand'        => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:255'],
            'price'        => ['required', 'integer', 'min:0', 'max:9999999'],
            'condition'    => ['required', Rule::in(['new', 'like_new', 'used'])],
            'status'       => ['required', Rule::in(['selling', 'sold'])],
            'temp_image'   => ['nullable','string'],
            'image'        => ['nullable', 'image', 'mimes:jpeg,png', 'max:4096'],
            'categories'   => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ]);

        return DB::transaction(function () use ($request, $item, $validated) {
            Storage::disk('public')->makeDirectory('images');

            $replaced = false;
            $tempName = trim((string) $request->input('temp_image', ''));

            if ($tempName !== '' && Storage::disk('public')->exists("temp/{$tempName}")) {
                if (!empty($item->image_path)) {
                    Storage::disk('public')->delete($item->image_path);
                }
                if (Storage::disk('public')->move("temp/{$tempName}", "images/{$tempName}")) {
                    $item->image_path = "images/{$tempName}";
                    $replaced = true;
                    session()->forget('temp_image');
                }
            }

            if (!$replaced && $request->hasFile('image')) {
                if (!empty($item->image_path)) {
                    Storage::disk('public')->delete($item->image_path);
                }
                $item->image_path = $request->file('image')->store('images', 'public');
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

            return redirect()->route('items.show', $item);
        });
    }

    /** 削除 */
    public function destroy(Item $item)
    {
        $this->authorize('delete', $item);

        if (!empty($item->image_path)) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->categories()->detach();
        $item->delete();

        return redirect()->route('items.index');
    }
}
