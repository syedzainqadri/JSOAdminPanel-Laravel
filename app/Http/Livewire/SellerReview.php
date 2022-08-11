<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Modules\Review\Entities\Review;

class SellerReview extends Component
{
    public $reviews, $user_id, $loadbutton = true, $total, $count = 5;
    public $loading = false;

    // Load More Data
    public function loadmore()
    {
        $this->loading = true;
        $this->count += 5;
    }

    public function render()
    {
        session(['seller_tab' => 'review_list']);

        $this->reviews = Review::with('user:id,name,image')->whereUserId($this->user_id)
            ->whereStatus(1)
            ->latest()
            ->take($this->count)
            ->get();
        $this->total = Review::whereUserId($this->user_id)->count();
        $this->loading = false;

        return view('livewire.seller-review');
    }
}
