from ESI import ESI_Base

class Methods(ESI_Base.Base):

    esiURL = "https://esi.evetech.net/"

    def alliances(self, arguments):

        return self.makeRequest(
            endpoint = "/alliances/{alliance_id}/",
            url = (self.esiURL + "latest/alliances/" + str(arguments["alliance_id"]) + "/?datasource=tranquility"),
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def corporations(self, arguments):

        return self.makeRequest(
            endpoint = "/corporations/{corporation_id}/",
            url = (self.esiURL + "latest/corporations/" + str(arguments["corporation_id"]) + "/?datasource=tranquility"),
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def characters(self, arguments):

        return self.makeRequest(
            endpoint = "/characters/{character_id}/",
            url = (self.esiURL + "latest/characters/" + str(arguments["character_id"]) + "/?datasource=tranquility"),
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def character_roles(self, arguments):

        return self.makeRequest(
            endpoint = "/characters/{character_id}/roles/",
            url = (self.esiURL + "latest/characters/" + str(arguments["character_id"]) + "/roles/?datasource=tranquility"),
            accessToken = self.accessToken,
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def character_notifications(self, arguments):

        return self.makeRequest(
            endpoint = "/characters/{character_id}/notifications/",
            url = (self.esiURL + "latest/characters/" + str(arguments["character_id"]) + "/notifications/?datasource=tranquility"),
            accessToken = self.accessToken,
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def character_affiliations(self, arguments):

        return self.makeRequest(
            endpoint = "/characters/affiliation/",
            url = (self.esiURL + "latest/characters/affiliation/?datasource=tranquility"),
            method = "POST",
            payload = arguments["characters"],
            cacheTime = 3600,
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def sovereignty_structures(self, arguments):

        return self.makeRequest(
            endpoint = "/sovereignty/structures/",
            url = (self.esiURL + "latest/sovereignty/structures/?datasource=tranquility"),
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def universe_names(self, arguments):

        return self.makeRequest(
            endpoint = "/universe/names/",
            url = (self.esiURL + "latest/universe/names/?datasource=tranquility"),
            method = "POST",
            payload = arguments["ids"],
            cacheTime = 3600,
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def universe_constellations(self, arguments):

        return self.makeRequest(
            endpoint = "/universe/constellations/{constellation_id}/",
            url = (self.esiURL + "latest/universe/constellations/" + str(arguments["constellation_id"]) + "/?datasource=tranquility"),
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def universe_moons(self, arguments):

        return self.makeRequest(
            endpoint = "/universe/moons/{moon_id}/",
            url = (self.esiURL + "latest/universe/moons/" + str(arguments["moon_id"]) + "/?datasource=tranquility"),
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def universe_planets(self, arguments):

        return self.makeRequest(
            endpoint = "/universe/planets/{planet_id}/",
            url = (self.esiURL + "latest/universe/planets/" + str(arguments["planet_id"]) + "/?datasource=tranquility"),
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def universe_regions(self, arguments):

        return self.makeRequest(
            endpoint = "/universe/regions/{region_id}/",
            url = (self.esiURL + "latest/universe/regions/" + str(arguments["region_id"]) + "/?datasource=tranquility"),
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def universe_structures(self, arguments):

        return self.makeRequest(
            endpoint = "/universe/structures/{structure_id}/",
            url = (self.esiURL + "latest/universe/structures/" + str(arguments["structure_id"]) + "/?datasource=tranquility"),
            accessToken = self.accessToken,
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def universe_systems(self, arguments):

        return self.makeRequest(
            endpoint = "/universe/systems/{system_id}/",
            url = (self.esiURL + "latest/universe/systems/" + str(arguments["system_id"]) + "/?datasource=tranquility"),
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )

    def universe_types(self, arguments):

        return self.makeRequest(
            endpoint = "/universe/types/{type_id}/",
            url = (self.esiURL + "latest/universe/types/" + str(arguments["type_id"]) + "/?datasource=tranquility"),
            retries = (arguments["retries"] if "retries" in arguments else 0)
        )
