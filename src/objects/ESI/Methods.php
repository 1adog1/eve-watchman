<?php

    namespace Ridley\Objects\ESI;

    class Methods extends Base {
        
        protected function character_affiliations(array $arguments) {
            
            return $this->makeRequest(
                endpoint: "/characters/affiliation/", 
                url: ($this->esiURL . "latest/characters/affiliation/?datasource=tranquility"), 
                method: "POST", 
                payload: $arguments["characters"], 
                cacheTime: 3600, 
                retries: (isset($arguments["retries"]) ? $arguments["retries"] : 0)
            );
            
        }
        
        protected function search(array $arguments) {
            
            $categories = implode(",", $arguments["categories"]);
            $search = urlencode($arguments["search"]);
            $language = (isset($arguments["language"]) ? ("&language=" . $arguments["language"]) : "");;
            $strict = (isset($arguments["strict"]) ? ("&strict=" . $arguments["strict"]) : "");
            
            $url = $this->esiURL . "latest/search/?datasource=tranquility&categories=" . $categories . "&search=" . $search . $language . $strict;
            
            return $this->makeRequest(
                endpoint: "/search/", 
                url: $url, 
                retries: (isset($arguments["retries"]) ? $arguments["retries"] : 0)
            );
            
        }
        
        protected function universe_names(array $arguments) {
            
            return $this->makeRequest(
                endpoint: "/universe/names/", 
                url: ($this->esiURL . "latest/universe/names/?datasource=tranquility"), 
                method: "POST", 
                payload: $arguments["ids"], 
                cacheTime: 3600, 
                retries: (isset($arguments["retries"]) ? $arguments["retries"] : 0)
            );
            
        }
        
    }

?>