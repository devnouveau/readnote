<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    #[Scope]
    protected function title(Builder $query, string $title): void
    {
        $query->where('title', 'LIKE', '%' . $title . '%');
    }

    /* TODO :
        메소드명, UI 혹은 기능 자체를 변경할지 검토해야 함.
        아래 스코프 메소드 모두 필터링이 아니라 정렬(리뷰수 순, 별점순 등)을 위한 메소드로
        기간에 상관없이 모든 도서를 조회하게 됨.
        그러나 UI상 노출되는 표현과 메소드명은 도서목록을 필터링해서 조회하는 기능을 의미하는 것처럼 보일 수 있으므로
        혼란을 방지하기 위해 어떤 식으로 변경할지 검토가 필요함.
    */
    #[Scope]
    protected function popular(Builder $query, $from = null, $to = null): void
    {
        $query->withCount([
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ])
            ->orderBy('reviews_count', 'desc'); // 특정기간 등록된 리뷰가 많은 순으로 정렬
    }

    #[Scope]
    protected function highestRated(Builder $query, $from = null, $to = null): void
    {
        $query->withAvg([
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ], 'rating')
            ->orderBy('reviews_avg_rating', 'desc'); // 특정기간 등록된 리뷰의 평균점이 많은 순으로 정렬
    }

    #[Scope]
    private function minReviews(Builder $query, int $minReviews): void
    {
        $query->having('reviews_count', '>=', $minReviews);
    }

    private function dateRangeFilter(Builder $query, $from = null, $to = null): void
    {
        if ($from && !$to) {
            $query->where('created_at', '>=', $from);
        } elseif (!$from && $to) {
            $query->where('created_at', '<=', $to);
        } elseif ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    }
}
