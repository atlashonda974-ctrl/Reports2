<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\BranchesList;
use Illuminate\Support\Facades\DB;
use App\Models\UnpostedLog;
class UnpostedReportsController extends Controller
{
    public function index()
    {
        $branches = BranchesList::all();
        
        return view('UnpostedReports.unposted_report', [
            'claims'          => [],
            'fromDate'        => date('Y-m-d', strtotime('-30 days')),
            'toDate'          => date('Y-m-d'),
            'showReport'      => false,
            'branches'        => $branches,
            'selectedBranch'  => 'All',
            'selectedDept'    => 'All',
            'selectedTakaful' => 'All',
            'departments'     => $this->getDepartmentsList(),
            'takafulOptions'  => ['All', 'Takaful', 'Non-Takaful']
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'fromDate' => 'required|date',
            'toDate'   => 'required|date|after_or_equal:fromDate'
        ]);
        
        $fromDate = $request->get('fromDate');
        $toDate   = $request->get('toDate');
        $branch   = $request->get('branch',     'All');
        $dept     = $request->get('department', 'All');
        $takaful  = $request->get('takaful',    'All');
        
        $fromDateFormatted = date('d-M-Y', strtotime($fromDate));
        $toDateFormatted   = date('d-M-Y', strtotime($toDate));
        
        try {
            $apiUrl  = "http://172.16.22.204/dashboardApi/uw/unposted.php";
            $date1   = new \DateTime($fromDate);
            $date2   = new \DateTime($toDate);
            $diffDays = $date1->diff($date2)->days;
            
            Log::info("UnpostedReport: Fetching data from {$fromDateFormatted} to {$toDateFormatted} ({$diffDays} days)" .
                   ($branch  != 'All' ? " for branch: {$branch}"     : " for all branches") .
                   ($dept    != 'All' ? " for department: {$dept}"   : "") .
                   ($takaful != 'All' ? " for takaful: {$takaful}"   : ""));
            
            $apiParams = [
                'datefrom' => $fromDateFormatted,
                'dateto'   => $toDateFormatted,
                'dept'     => $dept,
                'branch'   => $branch,
                'takaful'  => $takaful
            ];
            
            $response = Http::timeout(600)->get($apiUrl, $apiParams);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (empty($data)) {
                    Log::warning("UnpostedReport: No data found in API response", ['response' => $data]);
                    
                    return view('UnpostedReports.unposted_report', [
                        'claims'          => [],
                        'fromDate'        => $fromDate,
                        'toDate'          => $toDate,
                        'showReport'      => true,
                        'info'            => 'No unposted documents found for the selected criteria' .
                                             ($branch  != 'All' ? " and branch: {$branch}"       : '') .
                                             ($dept    != 'All' ? " and department: {$dept}"     : '') .
                                             ($takaful != 'All' ? " and takaful: {$takaful}"     : ''),
                        'branches'        => BranchesList::all(),
                        'selectedBranch'  => $branch,
                        'selectedDept'    => $dept,
                        'selectedTakaful' => $takaful,
                        'departments'     => $this->getDepartmentsList(),
                        'takafulOptions'  => ['All', 'Takaful', 'Non-Takaful'],
                        'totalRecords'    => 0,
                        'filteredRecords' => 0
                    ]);
                }
                
                $processedData = $this->processUnpostedData($data);
                $branches      = BranchesList::all();
                $totalRecords  = count($processedData);
                $totals        = $this->calculateTotals($processedData);
                
                if (!empty($processedData)) {
                    Log::info("UnpostedReport: First record structure", ['record' => $processedData[0]]);
                }
                
                Log::info("UnpostedReport: Found {$totalRecords} records" .
                       ($branch  != 'All' ? " for branch: {$branch}"     : '') .
                       ($dept    != 'All' ? " for department: {$dept}"   : '') .
                       ($takaful != 'All' ? " for takaful: {$takaful}"   : ''));
                
                $message = "Report generated successfully! Found {$totalRecords} unposted document(s)";
                if ($branch  != 'All') $message .= " for branch: {$branch}";
                if ($dept    != 'All') {
                    $deptName = $this->getDepartmentsList()[$dept] ?? $dept;
                    $message .= ", department: {$deptName}";
                }
                if ($takaful != 'All') $message .= ", takaful: {$takaful}";
                
                return view('UnpostedReports.unposted_report', [
                    'claims'          => $processedData,
                    'fromDate'        => $fromDate,
                    'toDate'          => $toDate,
                    'showReport'      => true,
                    'success'         => $message,
                    'branches'        => $branches,
                    'selectedBranch'  => $branch,
                    'selectedDept'    => $dept,
                    'selectedTakaful' => $takaful,
                    'departments'     => $this->getDepartmentsList(),
                    'takafulOptions'  => ['All', 'Takaful', 'Non-Takaful'],
                    'totalRecords'    => $totalRecords,
                    'filteredRecords' => $totalRecords,
                    'totals'          => $totals
                ]);

            } else {
                $statusCode   = $response->status();
                $errorMessage = "API Request Failed (Status: {$statusCode})";
                Log::error("UnpostedReport: API request failed with status {$statusCode}");
                
                return $this->viewWithError($errorMessage, $fromDate, $toDate, $branch, $dept, $takaful);
            }
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("UnpostedReport: Connection failed - " . $e->getMessage());
            return $this->viewWithError(
                'Connection Error: Unable to connect to the API server. Please check if the API server is running and accessible.',
                $fromDate, $toDate, $branch, $dept, $takaful
            );
            
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error("UnpostedReport: Request exception - " . $e->getMessage());
            return $this->viewWithError(
                'Request Timeout: The API request timed out. This may be due to a large date range or server issues. Please try a smaller date range or try again later.',
                $fromDate, $toDate, $branch, $dept, $takaful
            );
            
        } catch (\Exception $e) {
            Log::error("UnpostedReport: Unexpected error - " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->viewWithError(
                'Unexpected Error: ' . $e->getMessage() . '. Please contact support.',
                $fromDate, $toDate, $branch, $dept, $takaful
            );
        }
    }

    /**
     *  will return the error view without duplicating the array every time.
     */
    private function viewWithError($errorMessage, $fromDate, $toDate, $branch, $dept, $takaful)
    {
        return view('UnpostedReports.unposted_report', [
            'claims'          => [],
            'error'           => $errorMessage,
            'fromDate'        => $fromDate,
            'toDate'          => $toDate,
            'showReport'      => false,
            'branches'        => BranchesList::all(),
            'selectedBranch'  => $branch,
            'selectedDept'    => $dept,
            'selectedTakaful' => $takaful,
            'departments'     => $this->getDepartmentsList(),
            'takafulOptions'  => ['All', 'Takaful', 'Non-Takaful']
        ]);
    }

    /* ================================================================
       SEND EMAIL
       ================================================================ */
    public function sendEmail(Request $request)
    {
        try {
            $documents    = $request->input('documents',      []);
            $emailTo      = $request->input('email_to',       '');
            $emailCc      = $request->input('email_cc',       '');
            $emailSubject = $request->input('email_subject',  '');
            $emailBody    = $request->input('email_body',     '');   
            
            if (empty($documents)) {
                return response()->json(['success' => false, 'message' => 'No documents selected'], 400);
            }
            
            if (empty($emailTo)) {
                return response()->json(['success' => false, 'message' => 'No recipient email address provided'], 400);
            }
            
           
            ini_set("SMTP", "vqs3572.pair.com");
             $emailFrom = session('user.email', 'reports@ail.com'); // ← Get from session
        ini_set("sendmail_from", $emailFrom);

          
            $headers  = "From: AIL - Reports Portal <$emailFrom>\r\n";
            if (!empty($emailCc)) $headers .= "Cc: $emailCc\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $portalLink = 'http://192.168.170.24/Reports2/login';
            $userName   = session('user.name', 'System Administrator');
              $userEmail  = session('user.email', ''); 

          
            $message  = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
            $message .= '<div style="max-width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">';

         
            $headingText = !empty($emailSubject) ? htmlspecialchars($emailSubject) : 'Unposted Documents Alert';
            $message .= '<h2 style="color: #dc3545; border-bottom: 2px solid #dc3545; padding-bottom: 10px;">' . $headingText . '</h2>';

          
            if (!empty($emailBody)) {
                
                $allowedTags = '<p><br><b><strong><i><em><u><ul><ol><li><span><div><h1><h2><h3><h4><h5><h6><a><table><thead><tbody><tr><th><td>';
                $message .= strip_tags($emailBody, $allowedTags);
            } else {
                $message .= '<p>Dear Team,</p>';
                $message .= '<p>The following documents are currently unposted in the system. Kindly post them as soon as possible.</p>';
            }

         
            $message .= '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">';
            $message .= '<h3 style="color: #333; margin-top: 0;">Documents List (' . count($documents) . ' documents)</h3>';
            $message .= '<table style="width: 100%; border-collapse: collapse; background: white;">';
            $message .= '<thead>';
            $message .= '<tr style="background-color: #343a40; color: white;">';
            $message .= '<th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Sr#</th>';
            $message .= '<th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Document No</th>';
            $message .= '<th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Insured Name</th>';
            $message .= '<th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Gross Premium</th>';
            $message .= '<th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Department</th>';
            $message .= '</tr>';
            $message .= '</thead>';
            $message .= '<tbody>';
            
            foreach ($documents as $index => $doc) {
                $bgColor  = ($index % 2 == 0) ? '#ffffff' : '#f8f9fa';
                $message .= '<tr style="background-color: ' . $bgColor . ';">';
                $message .= '<td style="padding: 8px; border: 1px solid #dee2e6;">'             . ($index + 1) . '</td>';
                $message .= '<td style="padding: 8px; border: 1px solid #dee2e6;"><strong>'     . htmlspecialchars($doc['doc_no'])           . '</strong></td>';
                $message .= '<td style="padding: 8px; border: 1px solid #dee2e6;">'             . htmlspecialchars($doc['party_name'])        . '</td>';
                $message .= '<td style="padding: 8px; border: 1px solid #dee2e6;">'             . htmlspecialchars($doc['GDH_GROSSPREMIUM'])  . '</td>';
                $message .= '<td style="padding: 8px; border: 1px solid #dee2e6;">'             . htmlspecialchars($doc['dept'])              . '</td>';
                $message .= '</tr>';
            }
            
            $message .= '</tbody></table></div>';
            
            $message .= '<div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 20px 0;">';
            $message .= '<p style="margin: 0;"><strong>Action Required:</strong> Please post these documents at your earliest convenience.</p>';
            $message .= '</div>';
            
            // $message .= '<p><strong>Portal Link:</strong> <a href="' . $portalLink . '" style="color: #0062cc; text-decoration: none;">' . $portalLink . '</a></p>';
            $message .= '<p style="margin-top: 30px;">Best regards,<br><strong>' . htmlspecialchars($userName) . '</strong></p>';
            $message .= '</div></body></html>';

            if (empty($emailSubject)) {
                $emailSubject = "Unposted Documents Alert - " . count($documents) . " Document(s) Pending";
            }
$documentIds = array_map(function($doc) {
            return $doc['doc_no'] ?? $doc['GDH_DOC_REFERENCE_NO'] ?? null;
        }, $documents);
            $mailResult = mail($emailTo, $emailSubject, $message, $headers);
            
            if ($mailResult) {
                Log::info("UnpostedReport: Email sent successfully", [
                    'recipient_to'   => $emailTo,
                    'recipient_cc'   => $emailCc,
                    'subject'        => $emailSubject,
                    'document_count' => count($documents),
                    'sent_by'        => $userName,
                    'custom_body'    => !empty($emailBody)
                ]);
                UnpostedLog::create([
   'email_from'     => $userEmail,
    'recipient_to'   => $emailTo,
    'recipient_cc'   => $emailCc,
    'subject'        => $emailSubject,
    'email_body'     => $emailBody,
    'document_count' => count($documents),
    'documents'      => $documentIds,
    'sent_by'        => $userName
]);
                
                return response()->json([
                    'success'        => true,
                    'message'        => 'Email sent successfully',
                    'recipient_to'   => $emailTo,
                    'recipient_cc'   => $emailCc,
                    'document_count' => count($documents)
                ]);
            } else {
                Log::error("UnpostedReport: Failed to send email");
                return response()->json(['success' => false, 'message' => 'Failed to send email. Please check email configuration.'], 500);
            }

        } catch (\Exception $e) {
            Log::error("UnpostedReport: Error sending email - " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error sending email: ' . $e->getMessage()], 500);
        }
    }

   
    private function processUnpostedData($data)
    {
        $processedData = [];
        
        if (!is_array($data) || empty($data)) return $processedData;
        
        foreach ($data as $item) {
            if (is_object($item)) $item = (array)$item;
            
            $processedItem = [
                'STATUS'               => $item['STATUS']               ?? 'N/A',
                'PLC_LOC_CODE'         => $item['PLC_LOC_CODE']         ?? 'N/A',
                'PLC_LOCADESC'         => $item['PLC_LOCADESC']         ?? 'N/A',
                'PLC_LOC_GIAS2'        => $item['PLC_LOC_GIAS2']        ?? 'N/A',
                'PBC_BUSICLASS_CODE'   => $item['PBC_BUSICLASS_CODE']   ?? 'N/A',
                'PBC_DESC'             => $item['PBC_DESC']             ?? 'N/A',
                'PDP_DEPT_CODE'        => $item['PDP_DEPT_CODE']        ?? 'N/A',
                'PDT_DOCTYPE'          => $item['PDT_DOCTYPE']          ?? 'N/A',
                'GDH_BASEDOCUMENTNO'   => $item['GDH_BASEDOCUMENTNO']   ?? 'N/A',
                'GDH_DOC_REFERENCE_NO' => $item['GDH_DOC_REFERENCE_NO'] ?? 'N/A',
                'PPS_PARTY_CODE'       => $item['PPS_PARTY_CODE']       ?? 'N/A',
                'PPS_DESC'             => $item['PPS_DESC']             ?? 'N/A',
                'PDO_DEVOFFDESC'       => $item['PDO_DEVOFFDESC']       ?? 'N/A',
                'GDH_ISSUEDATE'        => $item['GDH_ISSUEDATE']        ?? 'N/A',
                'GDH_COMMDATE'         => $item['GDH_COMMDATE']         ?? 'N/A',
                'GDH_EXPIRYDATE'       => $item['GDH_EXPIRYDATE']       ?? 'N/A',
                'GDH_GROSSPREMIUM'     => isset($item['GDH_GROSSPREMIUM']) ? (float) $item['GDH_GROSSPREMIUM'] : 0,
                'GDH_TOTALSI'          => isset($item['GDH_TOTALSI'])      ? (float) $item['GDH_TOTALSI']      : 0,
                'GDH_NETPREMIUM'       => isset($item['GDH_NETPREMIUM'])   ? (float) $item['GDH_NETPREMIUM']   : 0,
                'GDH_PROTECT_TAG'      => $item['GDH_PROTECT_TAG']      ?? 'N',
                'BROKER'               => $item['BROKER']               ?? 'N/A',
            ];
            
            if (isset($processedItem['PDP_DEPT_CODE'])) {
                $deptCode = (string) $processedItem['PDP_DEPT_CODE'];
                $processedItem['DEPT_NAME'] = $this->getDepartmentsList()[$deptCode] ?? $deptCode;
            }
            
            $processedData[] = $processedItem;
        }
        
        return $processedData;
    }
    
    private function calculateTotals($data)
    {
        $totals = [
            'gross_premium' => 0,
            'total_si'      => 0,
            'net_premium'   => 0,
            'count'         => count($data)
        ];
        
        foreach ($data as $item) {
            $totals['gross_premium'] += $item['GDH_GROSSPREMIUM'] ?? 0;
            $totals['total_si']      += $item['GDH_TOTALSI']      ?? 0;
            $totals['net_premium']   += $item['GDH_NETPREMIUM']   ?? 0;
        }
        
        return $totals;
    }

    private function getDepartmentsList()
    {
        return [
            'All' => 'All Departments',
            '11'  => 'Fire',
            '12'  => 'Marine',
            '13'  => 'Motor',
            '14'  => 'Miscellaneous',
            '16'  => 'Health',
        ];
    }

    /* ================================================================
       REJECT INVOICE
       ================================================================ */
    public function rejectInvoice(Request $request)
    {
        try {
            $invoiceId = $request->input('invoice_id');
            $remark    = $request->input('remark', '');
            
            if (empty($invoiceId)) {
                return response()->json(['success' => false, 'message' => 'No invoice selected'], 400);
            }
            
            if (empty($remark)) {
                return response()->json(['success' => false, 'message' => 'Remark is required for rejection'], 400);
            }
            
            DB::table('invoices')->where('id', $invoiceId)->update([
                'status'                  => 'rejected',
                'admin_remark'            => $remark,
                'admin_action_by'         => session('user.id'),
                'admin_action_at'         => now(),
                'certificate_rejected'    => true,
                'certificate_rejected_at' => now(),
                'updated_at'              => now()
            ]);
            
            Log::info("Invoice rejected successfully", [
                'invoice_id'  => $invoiceId,
                'remark'      => $remark,
                'rejected_by' => session('user.name')
            ]);
            
            return response()->json(['success' => true, 'message' => 'Invoice rejected successfully']);

        } catch (\Exception $e) {
            Log::error("Error rejecting invoice - " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error rejecting invoice: ' . $e->getMessage()], 500);
        }
    }

public function emailLogs()
{
    try {
        $logs = UnpostedLog::orderBy('created_at', 'ASC')->get();
        return response()->json(['success' => true, 'logs' => $logs]);
    } catch (\Exception $e) {
        Log::error("UnpostedReport: Error fetching email logs - " . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Error fetching logs: ' . $e->getMessage()], 500);
    }
}
    
}