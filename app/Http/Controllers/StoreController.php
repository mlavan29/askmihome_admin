<?php
/**
 * File name: StoreController.php
 * Last modified: 2020.04.30 at 08:21:08
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers;

class StoreController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
	  public function index()
    {

        return view("stores.index");
    }
    public function vendors()
    {
        return view("vendors.index");
    }


  public function edit($id)
    {
    	    return view('stores.edit')->with('id',$id);
    }

    public function view($id)
    {
        return view('stores.view')->with('id',$id);
    }

    public function payout($id)
    {
        return view('stores.payout')->with('id',$id);
    }

    public function items($id)
    {
        return view('stores.items')->with('id',$id);
    }

    public function orders($id)
    {
        return view('stores.orders')->with('id',$id);
    }

    public function reviews($id)
    {
        return view('stores.reviews')->with('id',$id);
    }

    public function promos($id)
    {
        return view('stores.promos')->with('id',$id);
    }

    public function create(){
        return view('stores.create');
    }


}
