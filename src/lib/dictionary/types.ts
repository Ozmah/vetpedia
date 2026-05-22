import type {
	reviewStatuses,
	sectionKeys,
	speciesValues,
	unitKinds,
} from "../../server/db/schema";

export type DictionaryStatus = (typeof reviewStatuses)[number];
export type DictionaryKind = (typeof unitKinds)[number];
export type DictionarySpecies = (typeof speciesValues)[number];
export type DictionarySectionKey = (typeof sectionKeys)[number];

export type DictionarySectionInput = {
	key: DictionarySectionKey;
	title: string;
	text: string;
};

export type DictionaryUnitInput = {
	id?: string;
	title: string;
	titleEs?: string;
	kind: DictionaryKind;
	status: DictionaryStatus;
	aliases: string[];
	species: DictionarySpecies[];
	sections: DictionarySectionInput[];
};

export type DictionaryUnitSummary = {
	id: string;
	title: string;
	titleEs: string | null;
	kind: DictionaryKind;
	status: DictionaryStatus;
	aliases: string[];
	species: DictionarySpecies[];
	sectionCount: number;
	updatedAt: string;
};

export type DictionaryUnitDetail = DictionaryUnitSummary & {
	sections: DictionarySectionInput[];
};

export type DictionaryOverview = {
	count: number;
	units: DictionaryUnitSummary[];
};
