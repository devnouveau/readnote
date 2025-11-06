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


    #[Scope]
    protected function popular(Builder $query, $from = null, $to = null): void // TODO: 필터링이 아니라 정렬을 위한 메소드이므로 추후 메소드명 변경 필요
    {
        $query->withCount([
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ])
            ->orderBy('reviews_count', 'desc'); // 특정기간 등록된 리뷰가 많은 순으로 정렬
    }

    #[Scope]
    protected function highestRated(Builder $query, $from = null, $to = null): void // TODO: 필터링이 아니라 정렬을 위한 메소드이므로 추후 메소드명 변경 필요
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
