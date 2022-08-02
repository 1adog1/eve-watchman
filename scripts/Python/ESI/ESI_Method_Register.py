from ESI import ESI_Methods

class MethodRegister(ESI_Methods.Methods):

    def initalizeMethodList(self):

        self.methodList = {}

        self.register(
            endpoint = "/alliances/{alliance_id}/",
            method = "alliances",
            requiredArguments = ["alliance_id"]
        )

        self.register(
            endpoint = "/corporations/{corporation_id}/",
            method = "corporations",
            requiredArguments = ["corporation_id"]
        )

        self.register(
            endpoint = "/characters/{character_id}/",
            method = "characters",
            requiredArguments = ["character_id"]
        )

        self.register(
            endpoint = "/characters/{character_id}/roles/",
            method = "character_roles",
            requiredArguments = ["character_id"]
        )

        self.register(
            endpoint = "/characters/{character_id}/notifications/",
            method = "character_notifications",
            requiredArguments = ["character_id"]
        )

        self.register(
            endpoint = "/characters/affiliation/",
            method = "character_affiliations",
            requiredArguments = ["characters"]
        )


        self.register(
            endpoint = "/sovereignty/structures/",
            method = "sovereignty_structures",
            requiredArguments = []
        )

        self.register(
            endpoint = "/universe/names/",
            method = "universe_names",
            requiredArguments = ["ids"]
        )

        self.register(
            endpoint = "/universe/constellations/{constellation_id}/",
            method = "universe_constellations",
            requiredArguments = ["constellation_id"]
        )

        self.register(
            endpoint = "/universe/moons/{moon_id}/",
            method = "universe_moons",
            requiredArguments = ["moon_id"]
        )

        self.register(
            endpoint = "/universe/planets/{planet_id}/",
            method = "universe_planets",
            requiredArguments = ["planet_id"]
        )

        self.register(
            endpoint = "/universe/regions/{region_id}/",
            method = "universe_regions",
            requiredArguments = ["region_id"]
        )

        self.register(
            endpoint = "/universe/structures/{structure_id}/",
            method = "universe_structures",
            requiredArguments = ["structure_id"]
        )

        self.register(
            endpoint = "/universe/systems/{system_id}/",
            method = "universe_systems",
            requiredArguments = ["system_id"]
        )

        self.register(
            endpoint = "/universe/types/{type_id}/",
            method = "universe_types",
            requiredArguments = ["type_id"]
        )

    def register(self, endpoint, method, requiredArguments):

        self.methodList[endpoint] = {"Name": method, "Required Arguments": requiredArguments}
