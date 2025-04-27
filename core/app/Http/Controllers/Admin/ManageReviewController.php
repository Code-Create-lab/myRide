<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;

class ManageReviewController extends Controller
{
    public function reviews()
    {
        $pageTitle = 'All  Reviews';
        $reviews   = Review::with(['ride', 'ride.user', 'ride.driver', 'driver'])
            ->searchable(['ride:uid', 'user:username', 'driver:username'])
            ->orderBy("id", getOrderBy())
            ->paginate(getPaginate());
        return view('admin.reviews.all', compact('pageTitle', 'reviews'));
    }


    public function delete($id)
    {
        if (Review::destroy($id)) {
            return redirect()->back()->with('success', 'Review deleted successfully.');
        }
    
        return redirect()->back()->with('error', 'Review not found.');
    }
}
