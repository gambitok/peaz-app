<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ResponseController;
use App\Report;
use App\ReportStatus;
use Illuminate\Http\Request;

class ReportController extends ResponseController
{
    public function reportList()
    {
        $reportList = Report::all();
        $this->sendResponse(200, __('api.suc_report_list'), $reportList);
    }

    public function reportStatus(Request $request)
    {
        $user = $request->user();
        $rules = [
            "post_id" => ['required','exists:posts,id'],
            'report_id' => ['required','exists:reports,id'],
        ];
        $this->directValidation($rules);
        $report_status = ReportStatus::create([
            "user_id" => $user->id,
            "post_id" => $request->post_id,
            "report_id"=>$request->report_id,
            "status" => "resolve",
        ]);
        
        $data = $report_status::with('report')->get();
        
        $this->sendResponse(200, __('api.suc_report_status_list'), $data);
    }
}
