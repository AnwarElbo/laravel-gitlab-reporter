<?php
/**
 * Created by PhpStorm.
 * User: ivodebruijn
 * Date: 20/09/2017
 * Time: 21:58
 */

namespace Wyox\GitlabReport;

use Exception;
use Illuminate\Session\Store;

class Report
{
    private $session;
    private $form;
    private $get;
    private $exception;


    public function __construct(Exception $exception, $get = [], $form = [], Store $session)
    {
        $this->exception = $exception;
        $this->get = collect($get);
        // Filter all parameters from get, usually not needed or available in the form
        $this->form = collect($form)->diff($this->get);
        $this->session = $session;
    }

    public function render(){
        return $this->description();
    }

    public function description(){
        // Return html string in Gitlab flavoured markdown
        return $this->renderException() . $this->renderUrl() . $this->renderForm(). $this->renderSession();
    }

    /**
     * Generates a GitLab issue title
     * @return string
     */
    public function title(){
        return "BUG: " . $this->message() . " - "  . $this->signature();
    }

    /**
     * This returns a unique signature based on the exception, the query and input parameters
     * @return string
     */

    public function signature(){
        // Signature should be unique to the error (ignore session for now)
        $key = $this->message() . $this->exception->getFile() . $this->exception->getTraceAsString() . $this->exception->getCode();

        // This might fail if it has complex objects
        $key .= $this->form->implode('');
        $key .= $this->get->implode('');


        return hash('md5',$key);
    }

    /**
     * Returns a human readable severity code instead of a number. (e.g. E_NOTICE)
     * @return string
     */

    private function message(){
        $str = $this->exception->getMessage();

        if(empty($str)){
            $str = get_class($this->exception);
        }

        return $str;
    }

    private function renderForm(){
        $str = "#### Post Params\n\n```php\n";
        foreach($this->form as $key => $value){
            $str .= "FORM['" . $key . "'] = " . $this->renderValue($value) . "\n";
        }
        $str .= "```" . $this->newline();
        return $str;
    }

    private function renderUrl(){
        $str = "#### Url Params\n\n```php\n";
        foreach($this->get as $key => $value){
            $str .= "URL['" . $key . "'] = " . $this->renderValue($value) . "\n";
        }
        $str .= "```" . $this->newline();
        return $str;
    }

    private function renderSession(){
        $str = "#### Session Params\n\n```php\n";
        foreach($this->session as $key => $value){
            $str .= "SESSION['" . $key . "'] = " . $this->renderValue($value) . "\n";
        }
        $str .= "```" . $this->newline();
        return $str;
    }

    private function renderValue($value){
        if(is_string($value) || is_bool($value) || is_numeric($value) ){
            return $this->renderSimple($value);
        }else{
            $this->renderComplex($value);
        }
    }

    private function renderComplex(){
        return " 'COMPLEX VALUE' ";
    }

    private function renderSimple($value){
        return $value;
    }

    private function renderException(){

        return <<<EOF
#### Error summary
**File** {$this->exception->getFile()}

**Message** {$this->message()}

**Trace** 
```php
$this->exception->getTraceAsString()
```


EOF;

    }

    private function newline(){
        return "\n\r\n\r";
    }


}