<?php
namespace Membership;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Slim\Http\Request;
use Slim\Flash\Messages as FlashMessage;

class ViewExtension implements ExtensionInterface
{
    /**
     * Slim Request Object
     *
     * @var \Slim\Http\Request
     */
    protected $request;

    /**
     * Slim Request Object
     *
     * @var \Slim\Flash\Messages
     */
    protected $flash;

    /**
     * View Extention
     *
     * @param Slim\Http\Request $request
     */
    public function __construct(Request $request, FlashMessage $flash)
    {
        $this->request = $request;
        $this->flash = $flash;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Engine $engine)
    {
        // Add app view data
        $engine->addData([
            'validation_errors' => [],
            'base_js'  => [],
            'base_css' => [],
        ]);

        $engine->registerFunction('requestParam', [$this, 'getDefaultValue']);
        $engine->registerFunction('formInputSelect', [$this, 'inputSelect']);
        $engine->registerFunction('formErrorClass', [$this, 'errorClass']);
        $engine->registerFunction('formShowErrors', [$this, 'showError']);
        $engine->registerFunction('userPhoto', [$this, 'userPhoto']);
        $engine->registerFunction('flashMessages', [$this->flash, 'getMessages']);

        // Register validation helpers
        $engine->registerFunction('validationErrors', function (array $errors = []) use ($engine) {
            $engine->addData(['validation_errors' => $errors]);
        });

        // Register view js helpers
        $engine->registerFunction('appendJs', function (array $jsFiles = []) use ($engine) {
            $engine->addData(['base_js' => $jsFiles]);
        });

        // Register view css helpers
        $engine->registerFunction('appendCss', function (array $cssFiles = []) use ($engine) {
            $engine->addData(['base_css' => $cssFiles]);
        });
    }

    public function getDefaultValue($name, $default = null)
    {
        $this->request->getParam($name, $default);
    }

    public function userPhoto($public_id = null, $options = [])
    {
        $default = $this->baseUrl().'/public/images/team.png';
        if (null === $public_id) {
            return $default;
        }

        try {
            $options += [
                'tags' => 'user-avatar',
                'crop' => 'fill',
            ];

            $cdn_upload_path = 'phpindonesia/'.$this->settings['mode'].'/';
            return \Cloudinary::cloudinary_url($cdn_upload_path.$public_id, $options);

        } catch (\Exception $e) {
            return $default;
        }
    }

    public function inputSelect($name, array $data, array $attributes = [])
    {
        $default = isset($attributes['default']) ? $attributes['default'] : null;
        $reqParam = $this->request->getParam($name, $default);
        unset($attributes['default']);

        $attrs = [];
        foreach ($attributes as $key => $value) {
            $attrs[] = $key.'="'.$value.'"';
        }

        $elements = [
            '<select name="'.$name.'"'.($attrs ? implode(' ', $attrs) : '').'>',
            '<option value="" >-- Pilih --</option>'
        ];

        foreach ($data as $key => $value) {
            $selected = '';
            if ($key == $reqParam) {
                $selected = ' selected="selected"';
            }

            $elements[] = '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
        }

        $elements[] = '</select>';
        return implode('', $elements);
    }

    public function errorClass($name, $error_css_class, array $errors)
    {
        if (is_array($errors)) {
            if (isset($errors[$name])) {
                return $error_css_class;
            }
        }

        return '';
    }

    public function showError($name, array $errors)
    {
        $errors_str = '';
        if (is_array($errors)) {
            if (isset($errors[$name])) {
                foreach ($errors[$name] as $item) {
                    $errors_str .= '<label class="error">'.$item.'</label><br />';
                }
            }
        }

        return $errors_str;
    }
}