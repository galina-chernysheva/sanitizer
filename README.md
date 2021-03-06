﻿Валидация типов значений данных в формате JSON
===============================

## Что это такое
Это реализация валидатора данных JSON на основе схемы, декларативно описывающей структуру проверяемых данных. Язык описания схемы является подмножеством JSON Schema.
Валидатор написан на PHP 7.1.

Валидатор поддерживает проверку следующих типов:
* базовые:
    * "integer" (целое число), 
    * "float" (вещественное число), 
    * "string" (строка),
    * "array" (индексированный массив, список),
    * "object" (ассоциативный массив, объект),
* кастомные:
    * "phone" (телефонный номер РФ).
    
Пример данных (JSON):
```json
{
    "int": "  -123",
    "float": "  123.45",
    "arr-of-str": ["  string", "true", "1", "0.2e+7", "8 (000) 111-22-33"],
    "arr": [1, "string", 0.2e+5, "", [0, 1, 2], null, {"01.02.2018": "-13", "02.02.2018": -15}],
    "arr-of-obj": [
        null,
        {"prop1": "some string", "prop2": 0.001, "prop3": 123},
        {"prop1": "hello", "prop2": 3.14159, "prop3": 0}
    ],
    "arr-of-phones": [null, "  +7 (000) 111 - 22 33", "89001234567", "  8-9-0-0-4-4-4-5-5-6-6 "]
}
```

Пример схемы, описывающей эти данные (JSON):
```json
{
    "type": "object",
    "properties": {
        "int": {
            "type": "integer"
        },
        "float": {
            "type": "float"
        },
        "arr-of-str": {
            "type": "array",
            "items": {
                "type": "string"
            }
        },
        "arr": {
            "type": "array",
            "items": {}
        },
        "arr-of-obj": {
            "type": "array",
            "items": {
                "type": "object",
                "properties": {
                    "prop1": {
                        "type": "string"
                    },
                    "prop2": {
                        "type": "float"
                    },
                    "prop3": {
                        "type": "integer"
                    }
                }
            }
        },
        "arr-of-phones": {
            "type": "array",
            "items": {
                "type": "phone"
            }
        }
    }
}
```

Пример вызова для указанных выше данных ($rawData) и схемы ($schema):
```php
$customTypesMap = [
    'phone' => RuPhoneNumberTypeValidator::class      // добавляем поддержку нового типа - "phone"
];

$validator = new JsonSchemaValidator($customTypesMap);
$result = $validator->validate($schema, $rawData); 
```
Результат:
```php
[
    'errors' => [],
    'data' => [
        'int' => -123,
        'float' => 123.45,
        'arr-of-str' => ['  string', 'true', '1', '0.2e+7', '8 (000) 111-22-33'],
        'arr' => [1, 'string', 20000, '', [0, 1, 2], null, ['01.02.2018' => '-13', '02.02.2018' => -15]],
        'arr-of-obj' => [
            null,
            ['prop1' => 'some string', 'prop2' => 0.001, 'prop3' => 123], 
            ['prop1' => 'hello', 'prop2' => 3.14159, 'prop3' => 0]
        ],
        'arr-of-phones' => [null, '70001112233', '79001234567', '79004445566']
    ] 
]
```

Строку с данными можно получить из тела POST запроса с помощью контроллера ValidationController:
```php
$controller = new ValidationController();
$controller->process($schema, $customTypes);
```


## Структура проекта:

1. Папка controllers:
содержит единственный контроллер - ValidationController, отвечающий за приём данных из POST запроса и выдачу результатов обработки данных.

2. Папка utils:
содержит непосредственно класс санитайзера-валидатора -  JsonSchemaValidator (\utils\validators) и вспомогательные классы (санитайзеры-валидаторы отдельных типов данных (\utils\types), классы исключений (\utils\exceptions)).

3. Папка tests:
содержит наборы тестов и тестовые данные для проверки функциональности валидаторов и контроллера.

4. Папка vhost:
содержит конфиг хоста (необходимо настроить для тестов контроллера - hosts, include конфига в nginx.conf).

5. autoload.php:
скрипт загрузки классов, содержит проектную константу.

6. index.php:
скрипт - входная точка для всех запросов к хосту. Для тестовых целей задаёт данные для контроллера.

7. runTests.php:
скрипт для запуска тестов из папки tests.


## Как работает:
Валидация данных запроса разделена на несколько уровней "ответственности". 
Контроллер обрабатывает HTTP-запрос и передаёт полученные данные в JsonSchemaValidator. Для проверки данных необходимо задать схему (в формате json), которая описывает типы их значений. Также необходимо задать словарь кастомных типов, которые должны поддерживаться при проверке данных. Валидатор схемы проверяет её целостность, а проверку типов конкретных значений делегирует отдельным классам - валидаторам типов.

Результатом работы валидатора типа является сконвертированное значение или исключение; JsonSchemaValidator - массив с ошибками и сконвертированными данными или исключение; контроллера - json-encoded результат работы валидатора схемы и установка статуса ответа (500 - исключение валидатора схемы, 200 - данные получены).

#### Возможности:
* NULL, пустые массив и объект являются корректными значениями.
* Можно задавать скалярные значения данных, например:
 
    данные
    ```json
    "+7 (900) 111 22 33"
    ```
    схема
    ```json
    { "type": "phone" }
    ```
* Тип элементов массива можно задать следующим образом: 
    ```json
    { 
        "type": "array", 
        "items": {
            "type": "integer"
        } 
    }
    ```
* Тип значений свойств объекта можно задать следующим образом: 
    ```json
    {
        "type": "object", 
        "properties": {
            "prop1": {
                "type": "float" 
            }, 
            "prop2": { 
                "type": "string"
            }
        }
    }
    ```
* Если при типе "array" не указан тип элементов "items", то значение пройдёт только проверку на принадлежность к типу "массив", проверка элементов будет пропущена. Пример:
    
    данные
    ```json
    [
        [1, 2, "string"],
        "not an array"
    ]
    ```
    схема
    ```json
    { 
        "type": "array",
        "items": {
            "type": "array"
        }
    }
    ```
    результат проверки
    ```php
    [
        'errors' => [
            '[1]: Value is not an indexed array.'
        ],
        'data' => [
            [1, 2, 'string'],
            'not an array'
        ]
    ]
    ```

* Если при типе "object" не указан состав ключей и их типы "properties", то пройдёт только проверка на то, что значение является объектом. Если при этом указан тип элементов "items", то будет осуществлена проверка всех свойств объекта на соответствие указанному типу. В противном случае никаких дополнительных проверок не последует. Пример:

    данные
    ```json
    {
        "just-obj": "not an object",
        "obj-int-props": {
            "prop1": 123,
            "prop2": 456,
            "prop3": "789",
            "prop4": "not valid integer"
        }
    }
    ```
    схема
    ```json
    {
        "type": "object",
        "properties": {
            "just-obj": {
                "type": "object"
            },
            "obj-int-props": {
                "type": "object",
                "items": "integer"
            }
        }
    }
    ```
    результат проверки
    ```php
    [
        'errors' => [
            'just-obj: Value is not an associative array.',
            'obj-int-props[prop4]: Value is not an integer.'
        ],
        'data' => [
            'just-obj' => 'not an object',
            'obj-int-props' => [
                'prop1' => 123,
                'prop2' => 456,
                'prop3' => 789,
                'prop4' => 'not valid integer'
            ]
        ]
    ]
    ```
* При проверке объекта с указанными "properties" проверяется наличие перечисленных свойств (если не найдено - ошибка), "лишние" свойства не проверяются и исключений не генерируют.
* Если у узла типа "object" схемы указаны одновременно "properties" и "items", то использоваться будет только "items".
* При проверке номера телефона из строки удаляется пунктуация, пробельные символы и знак +, после чего проверяется соответствие допустимому формату. Поэтому даже значение *" ++ 7 ((900)) -- 111 --22 ..; 33"* будет считаться корректным и после обработки преобразуется в *"79001112233"*.
* В сообщении об ошибке указывается путь к элементу данных, вызвавшему ошибку, например:
    ```json
    {
        "errors": [
            "obj[prop1][0]: Value is not an integer."
        ],
        "data": {
            "obj": {
                "prop1": ["not valid integer value", 1, 42, -15],
                ...
            }
        }
    }
    ```
* Валидацию базовых типов можно переопределить или расширить, задав новый - кастомный - тип, например:
    ```php
    // Новый тип данных - восьмеричные целые числа
    class OctalIntegerTypeValidator implements ITypeValidator
    {
        private static $_errorMessage = "Value is not octal integer";
    
        public static function validate($value)
        {
            if (is_null($value)) {
                return $value;
            }
            
            if (is_numeric($value)) {
                $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE  | FILTER_FLAG_ALLOW_OCTAL);
                if (!is_null($value)) {
                    return $value;
                }
            }
            throw new TypeValidationException(static::$_errorMessage);
        }  
    }
  
    $schema = '{
        "type": "object",
        "properties": {
            "prop1": {
                "type": "integer"
            },
            "prop2": {
                "type": "phone"
            }  
        }
    }';
    $rawData = '{
        "prop1": "1750",    // десятичный эквивалент - 1000
        "prop2": "8 (123) 456 78 90"
    }';
    $customTypesMap = [
        'integer' => OctalIntegerTypeValidator::class,    // переопределяем валидацию базового типа "integer"
        'phone' => RuPhoneNumberTypeValidator::class      // добавляем поддержку нового типа - "phone"
    ];

    $validator = new JsonSchemaValidator($customTypesMap);
    $result = $validator->validate($schema, $rawData);  
    ```
    
    результат валидации
    ```php
     [
        'errors' => [],
        'data' => [
            'prop1' => 1000,
            'prop2' => '71234567890'
        ] 
     ]
    ```
* Возможно использовать валидаторы типов (\utils\types) самостоятельно, например:
    ```php
    try {
        $value = FloatTypeValidator::validate('this is definitely not a float value'); 
    } catch (TypeValidationException as $e) {
        // обработка ошибки
    }
    ```
