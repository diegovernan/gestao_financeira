<?php

namespace App\Http\Controllers;

use App\Category;
use App\Transaction;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $month = empty($request->get('month')) ? date('m') : $request->get('month');
        $year = empty($request->get('year')) ? date('Y') : $request->get('year');

        $transactions = Transaction::where('user_id', auth()->user()->id)->whereMonth('date', $month)->whereYear('date', $year)->orderBy('date')->get();

        $categories = Category::where('user_id', auth()->user()->id)->latest()->get();

        $month_zero = Transaction::where('user_id', auth()->user()->id)->whereMonth('date', $month)->whereYear('date', $year)->whereType(0)->sum('value');
        $month_one = Transaction::where('user_id', auth()->user()->id)->whereMonth('date', $month)->whereYear('date', $year)->whereType(1)->sum('value');

        $year_zero = Transaction::where('user_id', auth()->user()->id)->whereYear('date', $year)->whereType(0)->sum('value');
        $year_one = Transaction::where('user_id', auth()->user()->id)->whereYear('date', $year)->whereType(1)->sum('value');

        $all_zero = Transaction::where('user_id', auth()->user()->id)->whereType(0)->sum('value');
        $all_one = Transaction::where('user_id', auth()->user()->id)->whereType(1)->sum('value');

        return view('home', compact('transactions', 'categories', 'month_zero', 'month_one', 'year_zero', 'year_one', 'all_zero', 'all_one'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories|min:2|max:20'
        ]);

        $category = new Category;
        $category->user_id = auth()->user()->id;
        $category->name = $request->name;

        $category->save();

        return redirect('home')->with('success', 'Categoria adicionada!');
    }

    public function storeTransaction(Request $request)
    {
        $request->validate([
            'category_id' => 'required',
            'type' => 'required|boolean',
            'date' => 'required|date|date_format:Y-m-d',
            'description' => 'required|string|min:2|max:20',
            'value' => 'required'
        ]);

        $transaction = new Transaction();
        $transaction->user_id = auth()->user()->id;
        $transaction->category_id = $request->category_id;
        $transaction->type = $request->type;
        $transaction->date = $request->date;
        $transaction->description = $request->description;
        $transaction->value = $request->value;

        $transaction->save();

        return redirect('home')->with('success', 'Transação adicionada!');
    }
}
