import type { CSSProperties } from "react";

export type DropdownOption = {
	description?: string;
	disabled?: boolean;
	kicker?: string;
	label: string;
	value: string;
};

export type DropdownAnchorStyle = CSSProperties & {
	"--dropdown-anchor-name": string;
};

export type DropdownFieldProps = {
	description?: string;
	disabled?: boolean;
	error?: string;
	id?: string;
	label: string;
};
