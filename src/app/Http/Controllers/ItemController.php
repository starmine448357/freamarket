<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ExhibitionRequest;
use Illuminate\Database\Eloquent\Builder;

class ItemController extends Controller
{
    /**
     * 検索・絞り込み条件をクエリに適用
     */
    private function applyFilters(Builder $items, Request $request): Builder
    {
        $q         = trim((string) $request->input('q', ''));
        $category  = $request->input('category');
        $min       = $request->input('min');
        $max       = $request->input('max');
        $condition = $request->input('condition');
        $unsold    = $request->boolean('unsold');

        if ($q !== '') {
            $items->where('title', 'like', "%{$q}%");
        }
        if ($category) {
            $items->whereHas('categories', fn ($cq) => $cq->where('categories.id', $category));
        }
        if ($min !== null && $min !== '') {
            $items->where('price', '>=', (int) $min);
        }
        if ($max !== null && $max !== '') {
            $items->where('price', '<=', (int) $max);
        }
        if ($condition) {
            $items->where('condition', $condition);
        }
        if ($unsold) {
            $items->where('status', 'selling');
        }

        return $items;
    }

    /**
     * 商品一覧を表示（?tab=mylist でマイリスト表示）
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'recommend');

        if ($tab === 'mylist') {
            // マイリスト
            $items = Item::query()
                ->with(['categories', 'purchase'])
                ->latest();

            if (Auth::check()) {
                $items->whereHas('likes', fn ($q) => $q->where('user_id', Auth::id()))
                      ->where('user_id', '!=', Auth::id());
            } else {
                // 未ログイン時は空一覧
                $items->whereRaw('1 = 0');
            }
        } else {
            // おすすめ
            $items = Item::query()
                ->where('user_id', '!=', Auth::id())
                ->with(['categories', 'purchase'])
                ->latest();
        }

        $this->applyFilters($items, $request);

        $items      = $items->paginate(12)->appends($request->query());
        $categories = Category::select('id', 'name')->orderBy('name')->get();

        return view('items.index', [
            'items'      => $items,
            'tab'        => $tab,
            'categories' => $categories,
            'filters'    => $request->only(['q', 'category', 'min', 'max', 'condition', 'unsold']),
        ]);
    }

    /**
     * 商品詳細を表示
     */
    public function show(Item $item)
    {
        $item->load(['user', 'categories', 'comments.user', 'likes'])
             ->loadCount(['likes', 'comments']);

        $liked = Auth::check()
            ? $item->likes()->where('user_id', Auth::id())->exists()
            : false;

        return view('items.show', compact('item', 'liked'));
    }

    /**
     * 出品フォームを表示
     */
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

    /**
     * 商品を新規登録
     */
    public function store(ExhibitionRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $validated) {
            Storage::disk('public')->makeDirectory('images');

            $path     = null;
            $tempName = trim((string) $request->input('temp_image', ''));

            // (1) temp から本保存へ移動
            if ($tempName !== '' && Storage::disk('public')->exists("temp/{$tempName}")) {
                if (Storage::disk('public')->move("temp/{$tempName}", "images/{$tempName}")) {
                    $path = "images/{$tempName}";
                    session()->forget('temp_image');
                }
            }

            // (2) 通常アップロード
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

            return redirect()->route('items.index')->with('status', '商品を出品しました！');
        });
    }

    /**
     * 編集フォームを表示
     */
    public function edit(Item $item)
    {
        $this->authorize('update', $item);

        $categories = Category::orderBy('name')->get();
        $selected   = $item->categories()->pluck('categories.id')->all();

        return view('items.edit', compact('item', 'categories', 'selected'));
    }

    /**
     * 商品を更新
     */
    public function update(ExhibitionRequest $request, Item $item)
    {
        $this->authorize('update', $item);
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $item, $validated) {
            Storage::disk('public')->makeDirectory('images');

            $replaced  = false;
            $tempName  = trim((string) $request->input('temp_image', ''));

            // (1) temp から差し替え
            if ($tempName !== '' && Storage::disk('public')->exists("temp/{$tempName}")) {
                if (!empty($item->image_path)) {
                    Storage::disk('public')->delete($item->image_path);
                }
                if (Storage::disk('public')->move("temp/{$tempName}", "images/{$tempName}")) {
                    $item->image_path = "images/{$tempName}";
                    $replaced         = true;
                    session()->forget('temp_image');
                }
            }

            // (2) 通常アップロード差し替え
            if (!$replaced && $request->hasFile('image')) {
                if (!empty($item->image_path)) {
                    Storage::disk('public')->delete($item->image_path);
                }
                $item->image_path = $request->file('image')->store('images', 'public');
            }

            $item->fill([
                'title'       => $validated['title'],
                'brand'       => $validated['brand'] ?? null,
                'description' => $validated['description'],
                'price'       => $validated['price'],
                'condition'   => $validated['condition'],
                'status'      => $validated['status'] ?? 'selling',
            ])->save();

            $item->categories()->sync($validated['categories'] ?? []);

            return redirect()->route('items.show', $item)->with('status', '商品情報を更新しました');
        });
    }

    /**
     * 商品を削除
     */
    public function destroy(Item $item)
    {
        $this->authorize('delete', $item);

        if (!empty($item->image_path)) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->categories()->detach();
        $item->delete();

        return redirect()->route('items.index')->with('status', '商品を削除しました');
    }
}
