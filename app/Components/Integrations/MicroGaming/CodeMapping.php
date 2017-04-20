<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/13/16
 * Time: 11:10 AM
 */

namespace App\Components\Integrations\MicroGaming;


use iHubGrid\ErrorHandler\Http\CodeMappingBase;

class CodeMapping extends CodeMappingBase
{
    const INVALID_AUTH = 'invalid_auth';
    const ACCOUNT_BANNED = 'account_banned';
    const USER_RESTRICTED = 'user_restricted';
    const USER_LIMIT = 'user_limit';

    public static function getMapping(){
        return [
            StatusCode::SERVER_ERROR => [
                'message'   => 'Неопределенная ошибка.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SERVER_ERROR],
                'default'   => true
            ],
            StatusCode::INVALID_TOKEN => [
                'message'   => 'Токен игрока недействителен.',
                'map'       => [],
                'attribute' => 'token',
                'meanings'  => [self::INVALID_TOKEN]
            ],
            StatusCode::TOKEN_EXPIRED => [
                'message'   => 'The player token expired.',
                'map'       => [],
                'attribute' => 'timestamp',
                'meanings'  => [self::TIME_EXPIRED]
            ],
            StatusCode::INVALID_AUTH => [
                'message'   => 'Учетные данные для аутентификации через API неверны.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::INVALID_AUTH]
            ],
            StatusCode::INVALID_USER => [
                'message'   => 'Ошибка при проверке логина. Неверное имя пользователя или пароль.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::INVALID_USER_ID]
            ],
            StatusCode::ACCOUNT_BLOCKED => [
                'message'   => 'Аккаунт заблокирован.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::ACCOUNT_BANNED]
            ],
            StatusCode::ACCOUNT_NOT_FOUND => [
                'message'   => 'Такого аккаунта не существует.',
                'map'       => [1024, 1410],
                'attribute' => null,
                'meanings'  => [self::USER_NOT_FOUND]
            ],
            StatusCode::RESTRICTED_USER => [
                'message'   => 'Для игрока действует самоограничение.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            StatusCode::ACCOUNT_EULA_NOT_ACCEPTED => [
                'message'   => 'Игрок еще не принял правила и условия.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            StatusCode::SHOW_PROTECTION => [
                'message'   => 'Обязательное отображение защиты игрока.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            StatusCode::BLOCKED_IP => [
                'message'   => 'Ограничение по IP-адресу',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            StatusCode::PASSWORD_EXPIRED => [
                'message'   => 'Срок действия пароля истек',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            StatusCode::ACCOUNT_RESTRICTION_TIMEOUT => [
                'message'   => 'Срок самоограничения истек, и игрок должен связаться с оператором, чтобы отменить ограничение. После этого активируется период игровой паузы.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            StatusCode::ACCOUNT_RESTRICTION_PAUSE => [
                'message'   => 'Срок самоограничения истек, но у игрока период игровой паузы.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            StatusCode::ACCOUNT_BLACKLISTED => [
                'message'   => 'Аккаунт находится в черном списке.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            StatusCode::ACCOUNT_DISABLED => [
                'message'   => 'Аккаунт игрока отключен. Относится только к регулируемым рынкам.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            StatusCode::ALREADY_PROCESSED => [
                'message'   => 'Уже обработан на основе других сведений.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::DUPLICATE]
            ],
            StatusCode::ACCOUNT_NO_MONEY => [
                'message'   => 'У игрока недостаточно средств.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::NO_MONEY]
            ],
            StatusCode::ACCOUNT_DAILY_LIMIT => [
                'message'   => 'Игрок превысил свой дневной безопасный лимит.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
            StatusCode::ACCOUNT_WEEKLY_LIMIT => [
                'message'   => 'Игрок превысил свой недельный безопасный лимит.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
            StatusCode::ACCOUNT_MONTHLY_LIMIT => [
                'message'   => 'Игрок превысил свой месячный безопасный лимит.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
            StatusCode::ACCOUNT_PLAYTIME_EXPIRED => [
                'message'   => 'Игрок превысил свою продолжительность игры.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
            StatusCode::ACCOUNT_LOSES_OVERDRAFT => [
                'message'   => 'Игрок превысил свой лимит проигрыша.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
            StatusCode::ACCOUNT_GAME_RESTRICTED => [
                'message'   => 'Игрок не допущен к данной игре.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
            StatusCode::EXTERNAL_NOT_FOUND => [
                'message'   => 'Внешняя система с таким именем не существует',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
        ];
    }
}