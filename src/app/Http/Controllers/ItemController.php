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

class ItemController extends Controller
{
    /** おすすめ一覧（初期表示） */
    public function index()
    {
        $items = Item::with(['categories', 'purchase'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('items.index', [
            'items' => $items,
            'tab'   => 'recommend',
        ]);
    }

    /** マイリスト */
    public function mylist()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $items = Item::with(['categories', 'purchase'])
            ->whereHas('likes', fn ($q) => $q->where('user_id', Auth::id()))
            ->where('user_id', '!=', Auth::id())
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('items.index', [
            'items' => $items,
            'tab'   => 'mylist',
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

    /**
     * 出品登録
     * - temp_image が“存在すれば” temp→images へ移動（publicディスク）
     * - 無ければそのまま直接アップロードにフォールバック
     */
    public function store(ExhibitionRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $validated) {
            Storage::disk('public')->makeDirectory('images');

            $path = null;
            $tempName = trim((string) $request->input('temp_image', ''));

            // temp が実在する場合のみ利用
            if ($tempName !== '' && Storage::disk('public')->exists("temp/{$tempName}")) {
                $moved = Storage::disk('public')->move("temp/{$tempName}", "images/{$tempName}");
                if ($moved) {
                    $path = "images/{$tempName}";
                    session()->forget('temp_image');
                }
            }

            // 直接アップロードへフォールバック
            if ($path === null && $request->hasFile('image')) {
                $path = $request->file('image')->store('images', 'public'); // => images/xxxx.jpg
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
                'condition'   => $validated['condition'], // 'new'|'like_new'|'used'
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

    /**
     * 更新
     * - temp_image が“存在すれば”差し替え（旧画像削除）
     * - 無ければ直接アップにフォールバック
     */
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

            // temp が実在する場合のみ差し替え
            if ($tempName !== '' && Storage::disk('public')->exists("temp/{$tempName}")) {
                if (!empty($item->image_path)) {
                    Storage::disk('public')->delete($item->image_path);
                }
                $moved = Storage::disk('public')->move("temp/{$tempName}", "images/{$tempName}");
                if ($moved) {
                    $item->image_path = "images/{$tempName}";
                    $replaced = true;
                    session()->forget('temp_image');
                }
            }

            // 直接アップロードへフォールバック
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
