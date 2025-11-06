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

    // TODO : 추후 모든 로컬 스코프 메소드 정의를 scope속성을 추가하는 방식으로 변경 필요
    public function scopeTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'LIKE', '%' . $title . '%');
    }

//    #[Scope]
//    protected function title(Builder $query, string $title): void
//    {
//        $query->where('title', 'LIKE', '%' . $title . '%');
//    }

}
