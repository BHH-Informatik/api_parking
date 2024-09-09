<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;

// @group Log
// API endpoints for Audit Logging
class LogController extends Controller {


    /**
     * @group Log
     *
     * Get Logs
     *
     * @authenticated
     * @queryParam limit integer optional Limit. Example: 50
     * @queryParam offset integer optional Offset. Example: 0
     *
     * @response 200 scenario="Success" {"message":"Getting log information was successful","log":[{"id":1,"user_id":1,"action":"User created","created_at":"2021-09-29T14:00:00.000000Z","updated_at":"2021-09-29T14:00:00.000000Z"}]}
     */
    public function index(Request $request) {


        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);

        $log = Log::limit($limit)->offset($offset)->get();

        return response()->json(['message' => 'Getting log information was successful', 'log' => $log], 200);
    }


}
