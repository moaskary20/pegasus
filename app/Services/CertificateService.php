<?php

namespace App\Services;

use App\Models\Certificate;
use Illuminate\Support\Facades\Storage;
use TCPDF;

class CertificateService
{
    /**
     * Generate PDF for certificate using TCPDF (better Arabic support)
     */
    public function generatePdf(Certificate $certificate): string
    {
        $data = $this->getCertificateData($certificate);
        
        // Create new PDF document in landscape orientation
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Pegasus Academy');
        $pdf->SetAuthor('Pegasus Academy');
        $pdf->SetTitle('شهادة إتمام الدورة - ' . $data['courseName']);
        $pdf->SetSubject('Certificate of Completion');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(false, 0);
        
        // Add a page
        $pdf->AddPage();
        
        // Enable RTL
        $pdf->setRTL(true);
        
        // Set font - using DejaVu Sans which has excellent Unicode/Arabic support
        $pdf->SetFont('dejavusans', '', 16);
        
        // Build HTML content for TCPDF
        $html = $this->buildCertificateHtml($data);
        
        // Write HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Return PDF as string
        return $pdf->Output('', 'S');
    }
    
    /**
     * Build HTML content for certificate (TCPDF compatible)
     */
    protected function buildCertificateHtml(array $data): string
    {
        return '
        <div style="text-align: center; direction: rtl; font-family: dejavusans;">
            <div style="border: 8px solid #667eea; padding: 60px; background: #fff; position: relative;">
                <div style="position: absolute; top: 40px; left: 40px; width: 120px; height: 120px; border: 4px solid #667eea; border-radius: 50%; background: rgba(102, 126, 234, 0.1); display: flex; align-items: center; justify-content: center;">
                    <div style="font-size: 14px; font-weight: bold; color: #667eea; text-align: center;">
                        ختم<br>أكاديمية<br>بيغاسوس
                    </div>
                </div>
                
                <div style="text-align: center; margin-bottom: 40px; border-bottom: 3px solid #667eea; padding-bottom: 20px;">
                    <div style="font-size: 48px; font-weight: bold; color: #667eea; margin-bottom: 10px;">شهادة إتمام</div>
                    <div style="font-size: 24px; color: #666;">Certificate of Completion</div>
                </div>
                
                <div style="text-align: center; padding: 40px 0;">
                    <div style="font-size: 28px; color: #333; margin-bottom: 30px;">' . htmlspecialchars($data['introText'], ENT_QUOTES, 'UTF-8') . '</div>
                    
                    <div style="font-size: 42px; font-weight: bold; color: #667eea; margin: 20px 0; padding: 15px; border-bottom: 2px solid #667eea; display: inline-block;">' . htmlspecialchars($data['studentName'], ENT_QUOTES, 'UTF-8') . '</div>
                    
                    <div style="font-size: 28px; color: #333; margin-bottom: 30px;">' . htmlspecialchars($data['completionText'], ENT_QUOTES, 'UTF-8') . '</div>
                    
                    <div style="font-size: 32px; font-weight: 600; color: #764ba2; margin: 20px 0;">' . htmlspecialchars($data['courseName'], ENT_QUOTES, 'UTF-8') . '</div>
                    
                    <div style="font-size: 28px; color: #333; margin-top: 40px;">' . htmlspecialchars($data['awardText'], ENT_QUOTES, 'UTF-8') . '</div>
                </div>
                
                <div style="margin-top: 60px; display: flex; justify-content: space-between;">
                    <div style="text-align: center; flex: 1;">
                        <div style="border-top: 2px solid #333; width: 200px; margin: 60px auto 10px;"></div>
                        <div style="font-size: 18px; color: #666;">' . htmlspecialchars($data['directorName'], ENT_QUOTES, 'UTF-8') . '</div>
                    </div>
                    
                    <div style="text-align: center; flex: 1;">
                        <div style="border-top: 2px solid #333; width: 200px; margin: 60px auto 10px;"></div>
                        <div style="font-size: 18px; color: #666;">' . htmlspecialchars($data['academicDirectorName'], ENT_QUOTES, 'UTF-8') . '</div>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd;">
                    <div style="font-size: 18px; color: #666; margin-top: 10px;">تاريخ الإصدار: ' . htmlspecialchars($data['issueDateArabic'], ENT_QUOTES, 'UTF-8') . '</div>
                    <div style="font-size: 12px; color: #999; font-family: monospace;">رقم الشهادة: ' . htmlspecialchars($data['uuid'], ENT_QUOTES, 'UTF-8') . '</div>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Save certificate PDF to storage
     */
    public function saveCertificatePdf(Certificate $certificate): string
    {
        // Ensure certificates directory exists
        Storage::disk('public')->makeDirectory('certificates');
        
        $pdfContent = $this->generatePdf($certificate);
        $filename = 'certificate_' . $certificate->uuid . '.pdf';
        $path = 'certificates/' . $filename;
        
        Storage::disk('public')->put($path, $pdfContent);
        
        return $path;
    }
    
    /**
     * Get certificate data for template
     */
    public function getCertificateData(Certificate $certificate): array
    {
        $certificate->load(['user', 'course']);
        
        return [
            'certificate' => $certificate,
            'studentName' => $certificate->user->name,
            'courseName' => $certificate->course->title,
            'issueDate' => $certificate->issued_at->format('Y-m-d'),
            'issueDateArabic' => $this->formatDateArabic($certificate->issued_at),
            'uuid' => $certificate->uuid,
            'introText' => $certificate->intro_text ?? 'هذا يثبت أن',
            'completionText' => $certificate->completion_text ?? 'قد أكمل بنجاح دورة',
            'awardText' => $certificate->award_text ?? 'وتم منحه هذه الشهادة تقديراً لجهوده وإنجازه',
            'directorName' => $certificate->director_name ?? 'المدير العام',
            'academicDirectorName' => $certificate->academic_director_name ?? 'المدير الأكاديمي',
        ];
    }
    
    /**
     * Format date in Arabic
     */
    protected function formatDateArabic($date): string
    {
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];
        
        $day = $date->format('d');
        $month = (int) $date->format('m');
        $year = $date->format('Y');
        
        return $day . ' ' . $months[$month] . ' ' . $year;
    }
}
