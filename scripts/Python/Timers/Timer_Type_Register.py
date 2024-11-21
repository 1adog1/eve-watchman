class TimerRegister(object):

    def __init__(self):

        self.typeList = {}

        self.register(
            type = "StructureLostShields",
            method = "CitadelLostShields"
        )

        self.register(
            type = "StructureLostArmor",
            method = "CitadelLostArmor"
        )

        self.register(
            type = "OrbitalReinforced",
            method = "OrbitalReinforced"
        )

        self.register(
            type = "SkyhookLostShields",
            method = "SkyhookReinforced"
        )

        self.register(
            type = "unknown notification type (282)",
            method = "SkyhookReinforced"
        )

    def register(self, type, method):

        self.typeList[type] = method
