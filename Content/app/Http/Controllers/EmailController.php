<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReinsuranceRequestEmail;
use App\Models\EmailLog;
use App\Models\VerifyLog;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{
    public function sendEmail(Request $request)
    {
        // ✅ Validate the incoming request
        $request->validate([
            'to' => 'required|email',
            'cc' => 'nullable|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'pdf_data' => 'nullable|string',
            'pdf_filename' => 'nullable|string',

            // Record data
            'record' => 'nullable|array',
            'record.reqNote' => 'nullable|string',
            'record.docDate' => 'nullable|string',
            'record.dept' => 'nullable|string',
            'record.businessDesc' => 'nullable|string',
            'record.insured' => 'nullable|string',
            'record.reinsParty' => 'nullable|string',
            'record.totalSumIns' => 'nullable|string',
            'record.riSumIns' => 'nullable|string',
            'record.share' => 'nullable|string',
            'record.totalPremium' => 'nullable|string',
            'record.riPremium' => 'nullable|string',
            'record.commDate' => 'nullable|string',
            'record.expiryDate' => 'nullable|string',
            'record.cp' => 'nullable|string',
            'record.convTakaful' => 'nullable|string',
            'record.posted' => 'nullable|string',
            'record.userName' => 'nullable|string',
            'record.acceptanceDate' => 'nullable|string',
            'record.warrantyPeriod' => 'nullable|string',
            'record.commissionPercent' => 'nullable|string',
            'record.commissionAmount' => 'nullable|string',
            'record.acceptanceNo' => 'nullable|string',
        ]);

        try {
            // ✅ Get the user's name from session
            $userid = Session::get('user')['name'] ?? 'Unknown';

            // Prepare email data
            $mailData = [
                'subject' => $request->subject,
                'body' => $request->body
            ];

            $mail = Mail::to($request->to);

            if ($request->filled('cc')) {
                $mail->cc($request->cc);
            }

            // ✅ Handle PDF attachment if provided
            if ($request->filled('pdf_data') && $request->filled('pdf_filename')) {
                $pdfContent = base64_decode($request->pdf_data);
                $tempPath = storage_path('app/temp/' . $request->pdf_filename);

                if (!file_exists(dirname($tempPath))) {
                    mkdir(dirname($tempPath), 0755, true);
                }

                file_put_contents($tempPath, $pdfContent);
                $mail->send(new ReinsuranceRequestEmail($mailData, $tempPath, $request->pdf_filename));

                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
            } else {
                $mail->send(new ReinsuranceRequestEmail($mailData));
            }

            // ✅ Log into EmailLog
            EmailLog::create([
                'sent_to' => $request->to,
                'sent_cc' => $request->cc,
                'subject' => $request->subject,
                'body' => $request->body,
                'datetime' => now(),
                'reqnote' => $request->record['reqNote'] ?? null,
                'repname' => $request->reportName, // Use the report name from the request
                'doc_date' => $request->record['docDate'] ?? null,
                'dept' => $request->record['dept'] ?? null,
                'business_desc' => $request->record['businessDesc'] ?? null,
                'insured' => $request->record['insured'] ?? null,
                'reins_party' => $request->record['reinsParty'] ?? null,
                'total_sum_ins' => $request->record['totalSumIns'] ?? null,
                'ri_sum_ins' => $request->record['riSumIns'] ?? null,
                'share' => $request->record['share'] ?? null,
                'total_premium' => $request->record['totalPremium'] ?? null,
                'ri_premium' => $request->record['riPremium'] ?? null,
                'comm_date' => $request->record['commDate'] ?? null,
                'expiry_date' => $request->record['expiryDate'] ?? null,
                'cp' => $request->record['cp'] ?? null,
                'conv_takaful' => $request->record['convTakaful'] ?? null,
                'posted' => $request->record['posted'] ?? null,
                'user_name' => $request->record['userName'] ?? null,
                'acceptance_date' => $request->record['acceptanceDate'] ?? null,
                'warranty_period' => $request->record['warrantyPeriod'] ?? null,
                'commission_percent' => $request->record['commissionPercent'] ?? null,
                'commission_amount' => $request->record['commissionAmount'] ?? null,
                'acceptance_no' => $request->record['acceptanceNo'] ?? null,
                'created_by' => $userid,
            ]);

            // ✅ Log into VerifyLog
            VerifyLog::create([
                'GCP_DOC_REFERENCENO' => $request->record['referenceNo'] ?? null,
                'PDP_DEPT_CODE' => $request->record['dept'] ?? null,
                'GCP_SERIALNO' => $request->record['serialNo'] ?? null,
                'GCP_ISSUEDATE' => $request->record['docDate'] ?? null,
                'GCP_COMMDATE' => $request->record['commDate'] ?? null,
                'GCP_EXPIRYDATE' => $request->record['expiryDate'] ?? null,
                'GCP_REINSURER' => $request->record['insured'] ?? null,
                'GCP_REISSUEDATE' => $request->record['reinsParty'] ?? null,
                'GCP_RECOMMDATE' => $request->record['businessDesc'] ?? null,
                'GCP_REEXPIRYDATE' => null, // Set as needed
                'GCP_COTOTALSI' => $request->record['totalSumIns'] ?? null,
                'GCP_COTOTALPREM' => $request->record['totalPremium'] ?? null,
                'GCP_REINSI' => $request->record['riSumIns'] ?? null,
                'GCP_REINPREM' => $request->record['riPremium'] ?? null,
                'GCP_COMMAMOUNT' => $request->record['commissionAmount'] ?? null,
                'GCP_POSTINGTAG' => null, // Set as needed
                'GCP_CANCELLATIONTAG' => null, // Set as needed
                'GCP_POST_USER' => $userid,
                'GCT_THEIR_REF_NO' => null, // Set as needed
                'datetime' => now(),
                'sent_to' => $request->to,
                'sent_cc' => $request->cc,
                'subject' => $request->subject,
                'body' => $request->body,
                'created_by' => $userid,
                'updated_by' => null, // Set as needed
            ]);
        // dd($request->record['reqNote'] ?? null);
            return response()->json([
                'success' => true,
                'message' => 'Email sent and logged successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending email and logging: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }
}