<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResource
    {
        $request->validate([
            'title' => 'sometimes|string',
            'source' => 'sometimes|string',
            'from' => 'sometimes|date',
            'to' => 'sometimes|date|after_or_equal:from',
        ]);
        $data = Article::query();

        if ($request->has('title')) {
            $data->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->has('source')) {
            $data->where('source', $request->source);
        }
        if ($request->has('from') && $request->has('to')) {
            $data->whereBetween('published_at', [$request->from, $request->to]);
        }
        return ArticleResource::collection($data->paginate(10));
    }

    public function show($slug): JsonResource
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        return new ArticleResource($article);
    }

    public function summary(Request $request)
    {
        return response()->json([
            'total_articles' => Article::count(),
            'total_sources' => Article::distinct('source')->count('source'),
            'new_articles' => Article::where('published_at', '>=', now()->format('Y-m-d'))->count(),
        ]);
    }
}
