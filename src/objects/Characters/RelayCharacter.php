<?php

namespace Ridley\Objects\Characters;

class RelayCharacter {

    public function __construct(
        public $id,
        public $name,
        public $status,
        public $corporationID,
        public $corporation,
        public $allianceID,
        public $allianceName,
        public $roles
    ) {}

    public function exportSimple() {

        $simpleData = [
            "ID" => htmlspecialchars($this->id),
            "Name" => $this->name,
            "Valid" => ($this->status === "Valid"),
            "Is Director" => (in_array("Director", $this->roles)),
            "Is Station Manager" => (in_array("Station_Manager", $this->roles))
        ];

        return $simpleData;

    }

    public function exportComplex() {

        $complexData = [
            "ID" => htmlspecialchars($this->id),
            "Name" => $this->name,
            "Status" => $this->status,
            "Roles" => $this->roles
        ];

        return $complexData;

    }

}

?>
