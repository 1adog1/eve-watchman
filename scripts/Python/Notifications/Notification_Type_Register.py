class TypeRegister(object):

    def __init__(self):

        self.typeList = {}

        self.register(
            type = "EntosisCaptureStarted",
            method = "EntosisCaptureStarted"
        )

        self.register(
            type = "SovCommandNodeEventStarted",
            method = "SovCommandNodeEventStarted"
        )

        self.register(
            type = "SovStructureReinforced",
            method = "SovStructureReinforced"
        )

        self.register(
            type = "SovStructureDestroyed",
            method = "SovStructureDestroyed"
        )

        self.register(
            type = "SovAllClaimAquiredMsg",
            method = "SovClaimAcquired"
        )

        self.register(
            type = "SovAllClaimLostMsg",
            method = "SovClaimLost"
        )

        self.register(
            type = "SovStructureSelfDestructRequested",
            method = "SovSelfDestructRequested"
        )

        self.register(
            type = "SovStructureSelfDestructFinished",
            method = "SovSelfDestructFinished"
        )

        self.register(
            type = "SovStructureSelfDestructCancel",
            method = "SovSelfDestructCancel"
        )

        self.register(
            type = "MoonminingExtractionStarted",
            method = "ExtractionStarted"
        )

        self.register(
            type = "MoonminingExtractionFinished",
            method = "ExtractionFinished"
        )

        self.register(
            type = "MoonminingExtractionCancelled",
            method = "ExtractionCancelled"
        )

        self.register(
            type = "MoonminingAutomaticFracture",
            method = "AutomaticFracture"
        )

        self.register(
            type = "MoonminingLaserFired",
            method = "ManualFracture"
        )

        self.register(
            type = "StructureAnchoring",
            method = "CitadelAnchoring"
        )

        self.register(
            type = "StructureUnanchoring",
            method = "CitadelUnanchoring"
        )

        self.register(
            type = "StructureOnline",
            method = "CitadelOnline"
        )

        self.register(
            type = "StructureWentHighPower",
            method = "HighPower"
        )

        self.register(
            type = "StructureWentLowPower",
            method = "LowPower"
        )

        self.register(
            type = "StructureImpendingAbandonmentAssetsAtRisk",
            method = "AbandonmentRisk"
        )

        self.register(
            type = "StructureFuelAlert",
            method = "CitadelFuelAlert"
        )

        self.register(
            type = "StructureServicesOffline",
            method = "CitadelServicesOffline"
        )

        self.register(
            type = "OwnershipTransferred",
            method = "OwnershipTransferred"
        )

        self.register(
            type = "StructuresReinforcementChanged",
            method = "CitadelReinforcementChanged"
        )

        self.register(
            type = "StructureUnderAttack",
            method = "CitadelUnderAttack"
        )

        self.register(
            type = "StructureLostShields",
            method = "CitadelLostShields"
        )

        self.register(
            type = "StructureLostArmor",
            method = "CitadelLostArmor"
        )

        self.register(
            type = "StructureDestroyed",
            method = "CitadelDestroyed"
        )

        self.register(
            type = "OrbitalAttacked",
            method = "OrbitalAttacked"
        )

        self.register(
            type = "OrbitalReinforced",
            method = "OrbitalReinforced"
        )

        self.register(
            type = "AllAnchoringMsg",
            method = "TowerAnchoring"
        )

        self.register(
            type = "TowerResourceAlertMsg",
            method = "TowerFuelAlert"
        )

        self.register(
            type = "TowerAlertMsg",
            method = "TowerUnderAttack"
        )

        self.register(
            type = "CorpTaxChangeMsg",
            method = "CorpTaxChange"
        )

        self.register(
            type = "CorpNewCEOMsg",
            method = "CorpNewCEO"
        )

        self.register(
            type = "CorpVoteCEORevokedMsg",
            method = "CEORightsRevoked"
        )

        self.register(
            type = "CorpVoteMsg",
            method = "CorpVote"
        )

        self.register(
            type = "CorpNewsMsg",
            method = "CorpNewsMessage"
        )

        self.register(
            type = "unknown notification type (281)",
            method = "SkyhookOnline"
        )

        self.register(
            type = "unknown notification type (282)",
            method = "SkyhookReinforced"
        )

        self.register(
            type = "unknown notification type (283)",
            method = "SkyhookUnderAttack"
        )

        self.register(
            type = "unknown notification type (285)",
            method = "SkyhookAnchoring"
        )


    def register(self, type, method):

        self.typeList[type] = method
