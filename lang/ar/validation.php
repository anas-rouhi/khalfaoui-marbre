<?php

// رسائل التحقق، أساسا لاستمارات لوحة الإدارة.
// Les règles absentes d'ici retombent sur l'anglais du framework.

return [
    'accepted' => 'يجب قبول حقل :attribute.',
    'alpha_dash' => 'يجب أن يحتوي حقل :attribute على حروف وأرقام وشرطات فقط.',
    'array' => 'يجب أن يكون حقل :attribute قائمة.',
    'boolean' => 'يجب أن تكون قيمة حقل :attribute صحيحة أو خاطئة.',
    'confirmed' => 'تأكيد حقل :attribute غير مطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => 'حقل :attribute ليس تاريخا صالحا.',
    'email' => 'يجب أن يكون حقل :attribute بريدا إلكترونيا صالحا.',
    'exists' => 'القيمة المختارة في حقل :attribute غير صالحة.',
    'file' => 'يجب أن يكون حقل :attribute ملفا.',
    'image' => 'يجب أن يكون حقل :attribute صورة.',
    'in' => 'القيمة المختارة في حقل :attribute غير صالحة.',
    'integer' => 'يجب أن يكون حقل :attribute عددا صحيحا.',
    'max' => [
        'array' => 'يجب ألا يحتوي حقل :attribute على أكثر من :max عناصر.',
        'file' => 'يجب ألا يتجاوز حجم الملف :attribute ‏:max كيلوبايت.',
        'numeric' => 'يجب ألا تتجاوز قيمة حقل :attribute ‏:max.',
        'string' => 'يجب ألا يتجاوز حقل :attribute ‏:max حرفا.',
    ],
    'mimes' => 'يجب أن يكون حقل :attribute ملفا من نوع: :values.',
    'mimetypes' => 'يجب أن يكون حقل :attribute ملفا من نوع: :values.',
    'min' => [
        'array' => 'يجب أن يحتوي حقل :attribute على :min عناصر على الأقل.',
        'file' => 'يجب أن يكون حجم الملف :attribute ‏:min كيلوبايت على الأقل.',
        'numeric' => 'يجب أن تكون قيمة حقل :attribute ‏:min على الأقل.',
        'string' => 'يجب أن يحتوي حقل :attribute على :min أحرف على الأقل.',
    ],
    'numeric' => 'يجب أن يكون حقل :attribute رقما.',
    'password' => [
        'letters' => 'يجب أن يحتوي حقل :attribute على حرف واحد على الأقل.',
        'mixed' => 'يجب أن يحتوي حقل :attribute على حرف كبير وحرف صغير على الأقل.',
        'numbers' => 'يجب أن يحتوي حقل :attribute على رقم واحد على الأقل.',
        'symbols' => 'يجب أن يحتوي حقل :attribute على رمز واحد على الأقل.',
        'uncompromised' => 'ظهرت قيمة :attribute في تسريب بيانات. يرجى اختيار قيمة أخرى.',
    ],
    'prohibited' => 'حقل :attribute ممنوع.',
    'regex' => 'صيغة حقل :attribute غير صالحة.',
    'required' => 'حقل :attribute إلزامي.',
    'same' => 'يجب أن يتطابق حقلا :attribute و:other.',
    'string' => 'يجب أن يكون حقل :attribute نصا.',
    'unique' => 'قيمة حقل :attribute مستعملة من قبل.',
    'uploaded' => 'تعذر رفع الملف :attribute.',
    'url' => 'يجب أن يكون حقل :attribute رابطا صالحا.',

    'attributes' => [],
];
