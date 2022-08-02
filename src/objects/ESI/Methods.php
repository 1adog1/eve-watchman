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

        protected function character_roles(array $arguments) {

            return $this->makeRequest(
                endpoint: "/characters/{character_id}/roles/",
                url: ($this->esiURL . "latest/characters/" . $arguments["character_id"] . "/roles/?datasource=tranquility"),
                accessToken: $this->accessToken,
                retries: (isset($arguments["retries"]) ? $arguments["retries"] : 0)
            );

        }

        protected function authenticated_search(array $arguments) {

            $categories = implode(",", $arguments["categories"]);
            $search = urlencode($arguments["search"]);
            $language = (isset($arguments["language"]) ? ("&language=" . $arguments["language"]) : "");;
            $strict = (isset($arguments["strict"]) ? ("&strict=" . $arguments["strict"]) : "");

            $url = $this->esiURL . "latest/characters/" . $arguments["character_id"] . "/search/?datasource=tranquility&categories=" . $categories . "&search=" . $search . $language . $strict;

            return $this->makeRequest(
                endpoint: "/characters/{character_id}/search/",
                url: $url,
                accessToken: $this->accessToken,
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

        protected function corporations(array $arguments) {

            return $this->makeRequest(
                endpoint: "/corporations/{corporation_id}/",
                url: ($this->esiURL . "latest/corporations/" . $arguments["corporation_id"] . "/"),
                retries: (isset($arguments["retries"]) ? $arguments["retries"] : 0)
            );

        }

        protected function alliance_corporations(array $arguments) {

            return $this->makeRequest(
                endpoint: "/alliances/{alliance_id}/corporations/",
                url: ($this->esiURL . "latest/alliances/" . $arguments["alliance_id"] . "/corporations/"),
                retries: (isset($arguments["retries"]) ? $arguments["retries"] : 0)
            );

        }

    }

?>
