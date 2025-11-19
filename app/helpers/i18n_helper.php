<?php

/**
 * Internationalization (i18n) Helper
 * Handles multi-language support
 *
 * @author Pakiparc Team
 * @version 1.0
 */

// Available languages
define('AVAILABLE_LANGUAGES', [
    'fr' => [
        'name' => 'Français',
        'native' => 'Français',
        'flag' => '🇫🇷',
        'direction' => 'ltr',
        'locale' => 'fr_FR',
        'date_format' => 'd/m/Y',
        'datetime_format' => 'd/m/Y H:i',
        'currency' => 'EUR',
        'currency_symbol' => '€'
    ],
    'en' => [
        'name' => 'English',
        'native' => 'English',
        'flag' => '🇬🇧',
        'direction' => 'ltr',
        'locale' => 'en_GB',
        'date_format' => 'm/d/Y',
        'datetime_format' => 'm/d/Y g:i A',
        'currency' => 'EUR',
        'currency_symbol' => '€'
    ],
    'es' => [
        'name' => 'Spanish',
        'native' => 'Español',
        'flag' => '🇪🇸',
        'direction' => 'ltr',
        'locale' => 'es_ES',
        'date_format' => 'd/m/Y',
        'datetime_format' => 'd/m/Y H:i',
        'currency' => 'EUR',
        'currency_symbol' => '€'
    ],
    'de' => [
        'name' => 'German',
        'native' => 'Deutsch',
        'flag' => '🇩🇪',
        'direction' => 'ltr',
        'locale' => 'de_DE',
        'date_format' => 'd.m.Y',
        'datetime_format' => 'd.m.Y H:i',
        'currency' => 'EUR',
        'currency_symbol' => '€'
    ],
    'ar' => [
        'name' => 'Arabic',
        'native' => 'العربية',
        'flag' => '🇸🇦',
        'direction' => 'rtl',
        'locale' => 'ar_SA',
        'date_format' => 'd/m/Y',
        'datetime_format' => 'd/m/Y H:i',
        'currency' => 'EUR',
        'currency_symbol' => '€'
    ]
]);

// Default language
define('DEFAULT_LANGUAGE', 'fr');

// Global translations array
$GLOBALS['translations'] = [];
$GLOBALS['current_language'] = null;

/**
 * Initialize i18n system
 */
function initI18n()
{
    $lang = getCurrentLanguage();
    loadTranslations($lang);
}

/**
 * Get current language
 */
function getCurrentLanguage()
{
    if ($GLOBALS['current_language']) {
        return $GLOBALS['current_language'];
    }

    // Check session
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }

    // Check user preference
    if (isset($_SESSION['user_id'])) {
        $userLang = getUserLanguage($_SESSION['user_id']);
        if ($userLang) {
            setLanguage($userLang);
            return $userLang;
        }
    }

    // Check browser language
    $browserLang = getBrowserLanguage();
    if ($browserLang) {
        return $browserLang;
    }

    return DEFAULT_LANGUAGE;
}

/**
 * Set current language
 */
function setLanguage($lang)
{
    if (!isset(AVAILABLE_LANGUAGES[$lang])) {
        $lang = DEFAULT_LANGUAGE;
    }

    $_SESSION['language'] = $lang;
    $GLOBALS['current_language'] = $lang;

    // Update user preference if logged in
    if (isset($_SESSION['user_id'])) {
        updateUserLanguage($_SESSION['user_id'], $lang);
    }

    // Load translations
    loadTranslations($lang);

    // Set locale
    $locale = AVAILABLE_LANGUAGES[$lang]['locale'];
    setlocale(LC_ALL, $locale . '.UTF-8', $locale, $lang);
}

/**
 * Load translations for language
 */
function loadTranslations($lang)
{
    $file = __DIR__ . '/../languages/' . $lang . '.php';

    if (file_exists($file)) {
        $GLOBALS['translations'] = require $file;
    } else {
        $GLOBALS['translations'] = [];
    }
}

/**
 * Translate key (main translation function)
 */
function t($key, $replacements = [])
{
    $translations = $GLOBALS['translations'];

    // Support nested keys with dot notation
    $keys = explode('.', $key);
    $value = $translations;

    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $key; // Return key if translation not found
        }
    }

    // Replace placeholders
    if (!empty($replacements)) {
        foreach ($replacements as $placeholder => $replacement) {
            $value = str_replace('{' . $placeholder . '}', $replacement, $value);
        }
    }

    return $value;
}

/**
 * Alias for t() function (translate)
 */
function __($key, $replacements = [])
{
    return t($key, $replacements);
}

/**
 * Translate and echo
 */
function _e($key, $replacements = [])
{
    echo t($key, $replacements);
}

/**
 * Get browser language
 */
function getBrowserLanguage()
{
    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        return null;
    }

    $browserLangs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

    foreach ($browserLangs as $browserLang) {
        $lang = substr($browserLang, 0, 2);
        if (isset(AVAILABLE_LANGUAGES[$lang])) {
            return $lang;
        }
    }

    return null;
}

/**
 * Get user language from database
 */
function getUserLanguage($userId)
{
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT language FROM users WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $userId);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['language'] ?? null;
}

/**
 * Update user language preference
 */
function updateUserLanguage($userId, $lang)
{
    $db = Database::getInstance()->getConnection();
    $sql = "UPDATE users SET language = :lang WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':lang', $lang);
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
}

/**
 * Get current language info
 */
function getCurrentLanguageInfo()
{
    $lang = getCurrentLanguage();
    return AVAILABLE_LANGUAGES[$lang];
}

/**
 * Check if language is RTL
 */
function isRTL()
{
    $info = getCurrentLanguageInfo();
    return $info['direction'] === 'rtl';
}

/**
 * Format date according to current language
 */
function formatDate($date, $includeTime = false)
{
    $info = getCurrentLanguageInfo();
    $format = $includeTime ? $info['datetime_format'] : $info['date_format'];

    if (is_string($date)) {
        $date = strtotime($date);
    }

    return date($format, $date);
}

/**
 * Format currency according to current language
 */
function formatCurrency($amount)
{
    $info = getCurrentLanguageInfo();
    return number_format($amount, 2, ',', ' ') . ' ' . $info['currency_symbol'];
}

/**
 * Get language switcher HTML
 */
function languageSwitcher()
{
    $current = getCurrentLanguage();

    $html = '<div class="language-switcher dropdown">';
    $html .= '<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">';
    $html .= AVAILABLE_LANGUAGES[$current]['flag'] . ' ' . AVAILABLE_LANGUAGES[$current]['native'];
    $html .= '</button>';
    $html .= '<ul class="dropdown-menu">';

    foreach (AVAILABLE_LANGUAGES as $code => $lang) {
        $active = $code === $current ? 'active' : '';
        $html .= '<li><a class="dropdown-item ' . $active . '" href="?set_lang=' . $code . '">';
        $html .= $lang['flag'] . ' ' . $lang['native'];
        $html .= '</a></li>';
    }

    $html .= '</ul></div>';

    return $html;
}

/**
 * Handle language switching from URL parameter
 */
function handleLanguageSwitch()
{
    if (isset($_GET['set_lang'])) {
        $lang = $_GET['set_lang'];
        if (isset(AVAILABLE_LANGUAGES[$lang])) {
            setLanguage($lang);

            // Redirect to same page without lang parameter
            $redirect = strtok($_SERVER['REQUEST_URI'], '?');
            header('Location: ' . $redirect);
            exit;
        }
    }
}

// Auto-initialize on include
initI18n();
handleLanguageSwitch();
