<?php

    namespace Ridley\Core\Exceptions;

    class UserInputException extends \Exception {

        private $inputs_with_expected = [];
        private $implementedIncorrectly = false;
        private $implementationMessages = [];

        /**
         * For use in API classes when parsing and validating ajax values.
         * 
         * @param   string|array $inputs                Names or descriptors for the input(s) that were invalid. Must be same type and (if array) length as $expected_values.
         * @param   string|array $expected_values       What was expected for the $input(s). Must be same type and (if array) length as $inputs.
         * @param   bool         $hard_coded_inputs     Were input(s) supposed to be hardcoded? Log entries show up in Yellow. DEFAULT: false
         * @param   bool         $value_missing         Were input(s) missing rather than invalid? DEFAULT: false
         * @param   bool         $value_not_found       Did otherwise valid inputs result in failed resource location? Sets HTTP Status Code to 404. Mutually exclusive with $hard_coded_inputs and $value_missing. DEFAULT: false
         * @param                $code                  From Exception Class
         * @param   Throwable    $previous              From Exception Class
         */
        public function __construct(
            private string|array $inputs,
            private string|array $expected_values,
            private bool $hard_coded_inputs = false,
            private bool $value_missing = false,
            private bool $value_not_found = false,
            $code = 0, 
            \Throwable $previous = null
        ) {

            $this->evaluateImplementation();
            $message = $this->buildMessage();

            parent::__construct($message, $code, $previous);

        }

        private function evaluateImplementation() {

            //Checks if mutually exclusive arguments are both set.
            if (($this->hard_coded_inputs and $this->value_not_found) or ($this->value_missing and $this->value_not_found)) {

                $this->implementationMessages[] = "value_not_found is mutually exclusive with hard_coded_inputs and value_missing.";
                $this->implementedIncorrectly = true;

            }

            //Checks if $inputs and $expected_values are formatted correctly.
            if (gettype($this->inputs) !== gettype($this->expected_values)) {

                $this->implementationMessages[] = "inputs and expected_values must be the same type.";
                $this->implementedIncorrectly = true;

            }
            else if (gettype($this->inputs) === "string") {

                $this->inputs_with_expected[$this->inputs] = $this->expected_values;
                
            }
            else if (gettype($this->inputs) === "array" and array_is_list($this->inputs) and array_is_list($this->expected_values)) {

                if (count($this->inputs) === count($this->expected_values) and count($this->inputs) > 0) {

                    foreach ($this->inputs as $key => $value) {

                        $this->inputs_with_expected[$this->inputs[$key]] = $this->expected_values[$key];

                    }

                }
                else {

                    $this->implementationMessages[] = "inputs and expected_values must contain the same number of elements.";
                    $this->implementedIncorrectly = true;

                }
                
            }
            else {

                $this->implementationMessages[] = "inputs and expected_values must be type string or list-style array.";
                $this->implementedIncorrectly = true;

            }

        }

        private function buildMessage(): string {

            if (!$this->implementedIncorrectly) {

                if ($this->value_not_found) {
                    $descriptor = "not found";
                }
                elseif ($this->value_missing) {
                    $descriptor = "missing";
                }
                else {
                    $descriptor = "invalid";
                }
                                
                if (count($this->inputs_with_expected) > 1) {

                    $message = "One or more of the following " . ($this->hard_coded_inputs ? "hardcoded " : "") . "inputs were " . $descriptor . " : \n";

                }
                else {

                    $message = "The following " . ($this->hard_coded_inputs ? "hardcoded " : "") . "input was " . $descriptor . " : \n";

                }

                foreach ($this->inputs_with_expected as $in => $expect) {
                    $message .= "\n" . $in . " (" . $expect . ")";
                }


            }
            else {

                $message = "
UserInputException was implemented incorrectly. Value Dump:

Inputs: " . print_r($this->inputs, true) . "
Expected Values: " . print_r($this->expected_values, true) . "

Hard Coded: " . print_r($this->hard_coded_inputs, true) . "
Value Missing: " . print_r($this->value_missing, true) . "
Value Not Found: " . print_r($this->value_not_found, true) . "
                ";

            }

            $message .= "\n\nFile: " . $this->getFile() . " \nLine: " . $this->getLine();

            return $message;

        }

        public function wasExceptionThrownIncorrectly() {
            return $this->implementedIncorrectly;
        }

        public function getImplementationMessages() {
            return $this->implementationMessages;
        }

        public function wasInputHardcoded() {
            return $this->hard_coded_inputs;
        }

        public function wasInputMissing() {
            return $this->value_missing;
        }

        public function wasInputNotFound() {
            return $this->value_not_found;
        }

    }

?>