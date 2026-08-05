<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>حافظة توريد - {{ $applicant->FULL_NAME }}</title>
    <!-- Tailwind CSS (via CDN for printing) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 5mm; /* تقليل حواف الصفحة لتوفير مساحة */
            }
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
            }
            #print-area {
                padding: 0 !important;
                box-shadow: none !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            /* تقليل المسافات والهوامش أثناء الطباعة */
            .mb-8, .mb-6 { margin-bottom: 10px !important; }
            .pb-4 { padding-bottom: 8px !important; }
            .p-8 { padding: 10px !important; }
            .p-4 { padding: 8px !important; }
            .p-3 { padding: 6px !important; }
            .p-2 { padding: 4px !important; }
            
            /* تصغير الخطوط والعناوين */
            .text-3xl { font-size: 1.25rem !important; padding: 4px 16px !important; }
            .text-xl { font-size: 1.1rem !important; }
            .text-lg { font-size: 0.95rem !important; }
            .font-bold { font-size: 0.9rem !important; }
            .text-sm { font-size: 0.8rem !important; }
            
            /* تصغير الشعار */
            img.w-24.h-24 {
                width: 4rem !important;
                height: 4rem !important;
            }
            
            /* تصغير هوامش الجدول */
            table th, table td {
                padding: 4px !important;
                font-size: 0.85rem !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans p-4">
    <div class="max-w-4xl mx-auto bg-white p-8 shadow-md" id="print-area">
        <!-- Header -->
        <div class="flex justify-between items-center border-b-2 border-gray-800 pb-4 mb-6">
            <div class="text-center leading-relaxed">
                <p class="font-bold">الجمهورية اليمنية</p>
                <p class="font-bold">{{ $applicant->university->U_NAME ?? '' }}</p>
                <p class="font-bold">{{ $applicant->university->MANAGEMENT ?? '' }}</p>
                <p class="font-bold">{{ $applicant->university->ADMINISTRATION ?? '' }}</p>
                <p class="font-bold">{{ $applicant->university->DEPARTMENT ?? '' }}</p>
            </div>
            <div class="text-center flex-grow">
                <h1 class="text-3xl font-bold border-2 border-gray-800 inline-block px-6 py-2 rounded-full">(حافظة التوريد)</h1>
            </div>
            <div class="text-center">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="شعار الجامعة" class="w-24 h-24 object-contain mx-auto">
                @endif
            </div>
        </div>

        <div class="text-center mb-6">
            <h3 class="text-xl font-bold bg-gray-200 py-2 inline-block px-8 rounded">{{ !empty(trim($applicant->university->GS_TITLE_PAYMENT ?? '')) ? $applicant->university->GS_TITLE_PAYMENT : 'حافظة رسوم التنسيق' }}</h3>
        </div>

        <!-- Student Info -->
        <div class="border-2 border-blue-200 p-4 rounded-lg mb-8 bg-blue-50">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="flex mb-2"><span class="w-32 font-bold text-gray-700">رقم التنسيق:</span> <span class="font-bold text-lg">{{ $applicant->APPLICANT_IDENT }}</span></div>
                    <div class="flex mb-2"><span class="w-32 font-bold text-gray-700">اسم الطالب:</span> <span class="font-bold">{{ $applicant->FULL_NAME }}</span></div>
                    <div class="flex mb-2"><span class="w-32 font-bold text-gray-700">رقم الجلوس:</span> <span class="font-bold">{{ $applicant->SEC_SCHOOL_SEATNO }}</span></div>
                    <div class="flex mb-2"><span class="w-32 font-bold text-gray-700">عام التخرج:</span> <span class="font-bold">{{ $applicant->SEC_SCHOOL_YEAR }}</span></div>
                </div>
                <div>
                    <div class="flex mb-2"><span class="w-32 font-bold text-gray-700">المجموع:</span> <span class="font-bold">{{ $applicant->SEC_SCHOOL_MARK }}</span></div>
                    <div class="flex mb-2"><span class="w-32 font-bold text-gray-700">المعدل:</span> <span class="font-bold">{{ $applicant->SEC_SCHOOL_RATE }}</span></div>
                    <div class="flex mb-2"><span class="w-32 font-bold text-gray-700">نوع الثانوية:</span> <span class="font-bold">{{ $applicant->SEC_SCHOOL_TYPE }}</span></div>
                </div>
            </div>
        </div>

        @if($appGroups->count() > 0)
            @foreach($appGroups as $group)
                <div class="border-2 border-gray-300 rounded-lg mb-8 overflow-hidden page-break-inside-avoid">
                    <div class="bg-gray-100 p-3 border-b-2 border-gray-300">
                        <h2 class="text-lg font-bold text-center text-blue-800">(رقم حافظة التوريد: {{ $group->APP_BILL_IDENT }})</h2>
                    </div>
                    
                    <!-- Group Header Table -->
                    <table class="w-full text-center border-b-2 border-gray-300">
                        <thead class="bg-gray-50 border-b border-gray-300">
                            <tr>
                                <th class="p-2 border-l border-gray-300 w-12">م</th>
                                <th class="p-2 border-l border-gray-300 w-1/4">رقم حافظة التوريد</th>
                                <th class="p-2 border-l border-gray-300 w-1/4">المبلغ</th>
                                <th class="p-2 border-l border-gray-300 w-1/4">نوع العملة</th>
                                <th class="p-2">البيان</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="p-2 border-l border-gray-300 font-bold">1</td>
                                <td class="p-2 border-l border-gray-300 font-bold text-lg text-blue-700">{{ $group->APP_BILL_IDENT }}</td>
                                <td class="p-2 border-l border-gray-300 font-bold text-lg">{{ number_format($group->APPLYING_COST, 2) }}</td>
                                <td class="p-2 border-l border-gray-300 font-bold">{{ $group->COST_TYPE }}</td>
                                <td class="p-2 font-bold text-sm">{{ $group->offerGroup->DESCRIPTION ?? '' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Applications within group -->
                    @if($group->applications->count() > 0)
                        <table class="w-full text-center mt-4">
                            <thead class="bg-blue-50 border-y border-gray-300">
                                <tr>
                                    <th class="p-2 border-l border-gray-300 w-12 text-sm text-gray-600">م</th>
                                    <th class="p-2 border-l border-gray-300 text-sm text-gray-600">اسم الكلية</th>
                                    <th class="p-2 border-l border-gray-300 text-sm text-gray-600">التخصص</th>
                                    <th class="p-2 text-sm text-gray-600">النظام الدراسي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group->applications as $index => $app)
                                    <tr class="border-b border-gray-200 last:border-b-0 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                        <td class="p-2 border-l border-gray-300">{{ $index + 1 }}</td>
                                        <td class="p-2 border-l border-gray-300">{{ $app->faculty->FACULTY_NAME ?? '' }}</td>
                                        <td class="p-2 border-l border-gray-300 font-bold">{{ $app->program->PROGRAM_NAME ?? '' }}</td>
                                        <td class="p-2">{{ $app->studyType->STUDYTYPE_NAME ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @endforeach
            
            <div class="mt-8 text-sm font-bold text-gray-700">
                <p>** {{ $applicant->university->GS_NOTE_PAYMENT ?? 'يمكنك تسديد المبلغ في أي فرع من فروع كاك بنك من خلال إدخال رقم الحافظة' }}</p>
            </div>
        @else
            <div class="bg-red-50 border-l-4 border-red-500 p-4 my-8">
                <h2 class="text-xl font-bold text-red-700 mb-2">لا توجد حوافظ جاهزة للسداد لهذا المتقدم، قد يكون هذا لأحد الأسباب التالية:</h2>
                <ul class="list-disc list-inside text-red-600 mr-4">
                    <li>لا توجد رغبات مضافة لهذا المتقدم</li>
                    <li>تم تسديد الرسوم كاملة بالفعل</li>
                </ul>
            </div>
        @endif

    </div>

    <!-- Actions -->
    <div class="max-w-4xl mx-auto mt-8 text-center no-print pb-12">
        <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg text-lg transition duration-200">
            طباعة حافظة التوريد
        </button>
        <br><br>
        <a href="{{ route('filament.admin.resources.applicants.view', ['record' => $applicant->APPLICANT_IDENT]) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
            عودة لملف المتقدم
        </a>
    </div>
</body>
</html>
