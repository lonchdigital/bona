<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Вы должны принять :attribute.',
    'accepted_if' => 'Поле :attribute должно быть принято, когда :other равно :value.',
    'active_url' => 'Поле :attribute не является правильным URL.',
    'after' => 'Поле :attribute должно содержать дату после :date.',
    'after_or_equal' => 'Поле :attribute должно содержать дату после или равную :date.',
    'alpha' => 'Поле :attribute может содержать только буквы.',
    'alpha_dash' => 'Поле :attribute может содержать только буквы, цифры, дефисы и подчеркивания.',
    'alpha_num' => 'Поле :attribute может содержать только буквы и цифры.',
    'array' => 'Поле :attribute должно быть массивом.',
    'ascii' => 'Поле :attribute может содержать только однобайтовые буквенно-цифровые символы.',
    'before' => 'Поле :attribute должно содержать дату до :date.',
    'before_or_equal' => 'Поле :attribute должно содержать дату до или равную :date.',
    'between' => [
        'array' => 'Поле :attribute должно содержать от :min до :max элементов.',
        'file' => 'Размер файла в поле :attribute должен быть от :min до :max килобайт.',
        'numeric' => 'Поле :attribute должно быть между :min и :max.',
        'string' => 'Текст в поле :attribute должен быть от :min до :max символов.',
    ],
    'boolean' => 'Поле :attribute должно быть логическим.',
    'confirmed' => 'Поле :attribute не совпадает с подтверждением.',
    'current_password' => 'Пароль неверный.',
    'date' => 'Поле :attribute не является датой.',
    'date_equals' => 'Поле :attribute должно быть датой, равной :date.',
    'date_format' => 'Поле :attribute не соответствует формату :format.',
    'decimal' => 'Поле :attribute должно содержать :decimal десятичных знаков.',
    'declined' => 'Поле :attribute должно быть отклонено.',
    'declined_if' => 'Поле :attribute должно быть отклонено, когда :other равно :value.',
    'different' => 'Поля :attribute и :other должны быть разными.',
    'digits' => 'Длина цифрового поля :attribute должна быть :digits.',
    'digits_between' => 'Длина цифрового поля :attribute должна быть между :min и :max.',
    'dimensions' => 'Поле :attribute содержит недопустимые размеры изображения.',
    'distinct' => 'Поле :attribute содержит значение, которое дублируется.',
    'doesnt_end_with' => 'Поле :attribute не может заканчиваться одним из следующих значений: :values.',
    'doesnt_start_with' => 'Поле :attribute не может начинаться с одного из следующих значений: :values.',
    'email' => 'Поле :attribute должно содержать корректный электронный адрес.',
    'ends_with' => 'Поле :attribute должно заканчиваться одним из следующих значений: :values',
    'enum' => 'Выбранное значение для :attribute некорректно.',
    'exists' => 'Выбранное значение для :attribute некорректно.',
    'file' => 'Поле :attribute должно содержать файл.',
    'failed' => 'Указанные учетные данные не совпадают с нашими записями.',
    'gt' => [
        'array' => 'Поле :attribute должно содержать более :value элементов.',
        'file' => 'Поле :attribute должно быть больше :value килобайт.',
        'numeric' => 'Поле :attribute должно быть больше :value.',
        'string' => 'Поле :attribute должно быть больше :value символов.',
    ],
    'gte' => [
        'array' => 'Поле :attribute должно содержать :value или более элементов.',
        'file' => 'Поле :attribute должно быть равным или больше :value килобайт.',
        'numeric' => 'Поле :attribute должно быть равным или больше :value.',
        'string' => 'Поле :attribute должно быть равным или больше :value символов.',
    ],
    'image' => 'Поле :attribute должно содержать изображение.',
    'in' => 'Выбранное значение для :attribute некорректно.',
    'in_array' => 'Значение поля :attribute не содержится в :other.',
    'integer' => 'Поле :attribute должно содержать целое число.',
    'ip' => 'Поле :attribute должно содержать IP-адрес.',
    'ipv4' => 'Поле :attribute должно содержать IPv4-адрес.',
    'ipv6' => 'Поле :attribute должно содержать IPv6-адрес.',
    'json' => 'Данные поля :attribute должны быть в формате JSON.',
    'lowercase' => 'Поле :attribute должно быть строкой в нижнем регистре.',
    'lt' => [
        'array' => 'Поле :attribute должно содержать менее :value элементов.',
        'file' => 'Поле :attribute должно быть меньше :value килобайт.',
        'numeric' => 'Поле :attribute должно быть меньше :value.',
        'string' => 'Поле :attribute должно быть меньше :value символов.',
    ],
    'lte' => [
        'array' => 'Поле :attribute должно содержать не более :value элементов.',
        'file' => 'Поле :attribute должно быть равным или меньше :value килобайт.',
        'numeric' => 'Поле :attribute должно быть равным или меньше :value.',
        'string' => 'Поле :attribute должно быть равным или меньше :value символов.',
    ],
    'mac_address' => 'Поле :attribute должно содержать MAC-адрес.',
    'max' => [
        'array' => 'Поле :attribute должно содержать не более :max элементов.',
        'file' => 'Файл в поле :attribute должен быть не больше :max килобайт.',
        'numeric' => 'Поле :attribute должно быть не больше :max.',
        'string' => 'Текст в поле :attribute должен иметь длину не больше :max.',
    ],
    'max_digits' => 'Поле :attribute не может содержать более :max цифр.',
    'mimes' => 'Поле :attribute должно содержать файл одного из типов: :values.',
    'mimetypes' => 'Поле :attribute должно содержать файл одного из типов: :values.',
    'min' => [
        'array' => 'Поле :attribute должно содержать не менее :min элементов.',
        'file' => 'Размер файла в поле :attribute должен быть не менее :min килобайт.',
        'numeric' => 'Поле :attribute должно быть не меньше :min.',
        'string' => 'Текст в поле :attribute должен содержать не менее :min символов.',
    ],
    'min_digits' => 'Поле :attribute должно содержать как минимум :min цифр.',
    'missing' => 'Поле :attribute должно быть отсутствующим.',
    'missing_if' => 'Поле :attribute должно быть отсутствующим, если :other равно :value.',
    'missing_unless' => 'Поле :attribute должно быть отсутствующим, если :other не является :value.',
    'missing_with' => 'Поле :attribute должно быть отсутствующим, если присутствует :values.',
    'missing_with_all' => 'Поле :attribute должно быть отсутствующим, если присутствуют :values.',
    'multiple_of' => 'Поле :attribute должно быть кратным :value.',
    'not_in' => 'Выбранное значение для :attribute некорректно.',
    'not_regex' => 'Формат поля :attribute неверный.',
    'numeric' => 'Поле :attribute должно быть числом.',
    'password' => [
        'letters' => 'Поле :attribute должно содержать как минимум одну букву.',
        'mixed' => 'Поле :attribute должно содержать как минимум одну заглавную и одну строчную букву.',
        'numbers' => 'Поле :attribute должно содержать как минимум одну цифру.',
        'symbols' => 'Поле :attribute должно содержать как минимум один символ.',
        'uncompromised' => 'Поле :attribute было скомпрометировано в ходе утечки данных. Выберите другое значение для :attribute.',
    ],
    'present' => 'Поле :attribute должно быть присутствующим.',
    'prohibited' => 'Поле :attribute запрещено.',
    'prohibited_if' => 'Поле :attribute запрещено, если :other равно :value.',
    'prohibited_unless' => 'Поле :attribute запрещено, если :other не находится в :values.',
    'prohibits' => 'Поле :attribute запрещает присутствие :other.',
    'regex' => 'Поле :attribute имеет неверный формат.',
    'required' => 'Поле :attribute является обязательным для заполнения.',
    'required_array_keys' => 'Поле :attribute должно содержать записи для: :values.',
    'required_if' => 'Поле :attribute является обязательным для заполнения, если :other равно :value.',
    'required_if_accepted' => 'Поле :attribute является обязательным, если :other принято.',
    'required_unless' => 'Поле :attribute является обязательным для заполнения, если :other отличается от :values.',
    'required_with' => 'Поле :attribute является обязательным для заполнения, если указано :values.',
    'required_with_all' => 'Поле :attribute является обязательным для заполнения, если указаны все :values.',
    'required_without' => 'Поле :attribute является обязательным для заполнения, если не указано :values.',
    'required_without_all' => 'Поле :attribute является обязательным для заполнения, если не указаны все :values.',
    'same' => 'Поля :attribute и :other должны совпадать.',
    'size' => [
        'array' => 'Поле :attribute должно содержать :size элементов.',
        'file' => 'Файл в поле :attribute должен иметь размер :size килобайт.',
        'numeric' => 'Поле :attribute должно иметь длину :size.',
        'string' => 'Текст в поле :attribute должен содержать :size символов.',
    ],
    'starts_with' => 'Поле :attribute должно начинаться с одного из следующих значений: :values.',
    'string' => 'Поле :attribute должно быть строкой.',
    'timezone' => 'Поле :attribute должно содержать корректную временную зону.',
    'unique' => 'Указанное значение поля :attribute уже существует.',
    'uploaded' => 'Не удалось загрузить :attribute.',
    'uppercase' => 'Поле :attribute должно быть строкой в верхнем регистре.',
    'url' => 'Неверный формат поля :attribute.',
    'ulid' => 'Поле :attribute должно быть корректным ULID.',
    'uuid' => 'Поле :attribute должно быть корректным UUID идентификатором.',
    'phone_number' => [
        'size' => 'Поле :attribute должно содержать :size символов.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Здесь вы можете указать пользовательские сообщения об ошибках валидации
    | для атрибутов, используя соглашение "attribute.rule" для названия строк.
    | Это позволяет быстро указывать конкретное пользовательское сообщение
    | для заданного правила атрибута.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Следующие языковые строки используются для замены заполнителя атрибута
    | на что-то более понятное для чтения, например, "Адрес электронной почты"
    | вместо "email". Это помогает сделать наше сообщение более выразительным.
    |
    */

    'attributes' => [],
];
