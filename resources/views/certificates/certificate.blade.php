<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شهادة إتمام الدورة</title>
    <style>
        @font-face {
            font-family: 'Cairo';
            src: url('file://{{ str_replace(["\\", " "], ["/", "%20"], storage_path("fonts/Cairo-Regular.ttf")) }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', 'DejaVu Sans', 'Arial Unicode MS', 'Tahoma', 'Arial', sans-serif;
            direction: rtl;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            unicode-bidi: embed;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }
        
        @page {
            margin: 0;
        }
        
        .certificate-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 60px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.2);
            position: relative;
            border: 8px solid #667eea;
        }
        
        .certificate-header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
        }
        
        .certificate-title {
            font-size: 48px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .certificate-subtitle {
            font-size: 24px;
            color: #666;
            font-weight: 400;
        }
        
        .certificate-body {
            text-align: center;
            padding: 40px 0;
        }
        
        .certificate-text {
            font-size: 28px;
            color: #333;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .student-name {
            font-size: 42px;
            font-weight: 700;
            color: #667eea;
            margin: 20px 0;
            padding: 15px;
            border-bottom: 2px solid #667eea;
            display: inline-block;
        }
        
        .course-name {
            font-size: 32px;
            font-weight: 600;
            color: #764ba2;
            margin: 20px 0;
        }
        
        .certificate-footer {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .signature-section {
            text-align: center;
            flex: 1;
        }
        
        .signature-line {
            border-top: 2px solid #333;
            width: 200px;
            margin: 60px auto 10px;
        }
        
        .signature-label {
            font-size: 18px;
            color: #666;
        }
        
        .certificate-info {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .certificate-uuid {
            font-size: 12px;
            color: #999;
            font-family: monospace;
        }
        
        .issue-date {
            font-size: 18px;
            color: #666;
            margin-top: 10px;
        }
        
        .decorative-border {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid #667eea;
            opacity: 0.3;
            pointer-events: none;
        }
        
        .seal {
            position: absolute;
            top: 40px;
            left: 40px;
            width: 120px;
            height: 120px;
            border: 4px solid #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(102, 126, 234, 0.1);
        }
        
        .seal-text {
            font-size: 14px;
            font-weight: 700;
            color: #667eea;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="decorative-border"></div>
        
        <div class="seal">
            <div class="seal-text">
                ختم<br>أكاديمية<br>بيغاسوس
            </div>
        </div>
        
        <div class="certificate-header">
            <div class="certificate-title">شهادة إتمام</div>
            <div class="certificate-subtitle">Certificate of Completion</div>
        </div>
        
        <div class="certificate-body">
            <div class="certificate-text">
                {{ $introText }}
            </div>
            
            <div class="student-name">
                {{ $studentName }}
            </div>
            
            <div class="certificate-text">
                {{ $completionText }}
            </div>
            
            <div class="course-name">
                {{ $courseName }}
            </div>
            
            <div class="certificate-text" style="margin-top: 40px;">
                {{ $awardText }}
            </div>
        </div>
        
        <div class="certificate-footer">
            <div class="signature-section">
                <div class="signature-line"></div>
                <div class="signature-label">{{ $directorName }}</div>
            </div>
            
            <div class="signature-section">
                <div class="signature-line"></div>
                <div class="signature-label">{{ $academicDirectorName }}</div>
            </div>
        </div>
        
        <div class="certificate-info">
            <div class="issue-date">
                تاريخ الإصدار: {{ $issueDateArabic }}
            </div>
            <div class="certificate-uuid">
                رقم الشهادة: {{ $uuid }}
            </div>
        </div>
    </div>
</body>
</html>
