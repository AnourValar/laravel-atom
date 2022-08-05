<?php

namespace AnourValar\LaravelAtom\Http\Middleware\Web;

use Closure;
use Illuminate\Http\Request;

class Select2
{
    /**
     * @var string
     */
    protected string $defaultTextAttribute = 'title';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! is_string($request->input('term'))) {
            return response()->json([]);
        }

        $strlen = mb_strlen(trim($request->input('term')));
        if ($strlen < 3 || $strlen > 100) {
            return response()->json([]);
        }

        $response = $next($request);
        $this->convertResponse($response, $request);

        return $response;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function convertResponse(\Symfony\Component\HttpFoundation\Response &$response, Request $request): void
    {
        $content = $response->getOriginalContent();

        if (! $content instanceof \Illuminate\Pagination\LengthAwarePaginator
            && ! $content instanceof \Illuminate\Pagination\CursorPaginator
            && ! $content instanceof \Illuminate\Pagination\Paginator
        ) {
            throw new \LogicException('Response type is not supported.');
        }

        $collection = [];
        foreach ($content->getCollection() as $item) {
            if ($item instanceof \Illuminate\Database\Eloquent\Model) {
                $item = ['id' => $item->getKey(), 'text' => $item->{$this->defaultTextAttribute}];
            }

            $collection[] = $item;
        }

        $body = [
            'results' => $collection,
            'pagination' => [
                'more' => $content->hasMorePages(),
            ],
        ];

        $response->setData($body);
    }
}
