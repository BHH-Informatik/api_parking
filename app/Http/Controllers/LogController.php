<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;

class LogController extends Controller {

    public function index(Request $request) {


        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);

        $log = Log::limit($limit)->offset($offset)->get();

        return response()->json(['message' => 'Getting log information was successful', 'log' => $log], 200);
    }


}
