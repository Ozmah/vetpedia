import type { KeyboardEvent } from "react";
import { useId, useRef, useState } from "react";
import { cn } from "../../../lib/cn";
import { FieldShell } from "../text-field";
import {
	DropdownCaret,
	dropdownControlClassName,
	dropdownPanelClassName,
	focusElement,
	getActiveDescendantId,
	getAnchorStyle,
	getDescribedBy,
	getInitialActiveIndex,
	getOptionLabel,
	handleListNavigation,
	optionBaseClassName,
	renderOptionContent,
	useAnchoredPopover,
} from "./dropdown-primitives";
import type { DropdownFieldProps, DropdownOption } from "./types";

interface SelectProps extends DropdownFieldProps {
	onValueChange: (value: string) => void;
	options: DropdownOption[];
	placeholder?: string;
	value: string | null;
}

export function Select({
	description,
	disabled,
	error,
	id,
	label,
	onValueChange,
	options,
	placeholder = "Selecciona una opción",
	value,
}: SelectProps) {
	const generatedId = useId();
	const fieldId = id ?? generatedId;
	const listboxId = `${fieldId}-listbox`;
	const descriptionId = description ? `${fieldId}-description` : undefined;
	const errorId = error ? `${fieldId}-error` : undefined;
	const listboxRef = useRef<HTMLDivElement>(null);
	const triggerRef = useRef<HTMLButtonElement>(null);
	const [activeIndex, setActiveIndex] = useState<number | null>(null);
	const { anchorName, handlePopoverToggle, open, popoverRef, setOpen } =
		useAnchoredPopover();
	const selectedLabel = getOptionLabel(options, value);

	function openList() {
		if (disabled) return;

		setActiveIndex(getInitialActiveIndex(options, value));
		setOpen(true);
		focusElement(listboxRef);
	}

	function closeList() {
		setOpen(false);
		focusElement(triggerRef);
	}

	function selectOption(index: number | null) {
		if (index === null) return;

		const option = options[index];

		if (!option || option.disabled) return;

		onValueChange(option.value);
		closeList();
	}

	function handleTriggerKeyDown(event: KeyboardEvent<HTMLButtonElement>) {
		if (["ArrowDown", "ArrowUp", "Enter", " "].includes(event.key)) {
			event.preventDefault();
			openList();
		}
	}

	return (
		<FieldShell
			description={description}
			descriptionId={descriptionId}
			error={error}
			errorId={errorId}
			id={fieldId}
			label={label}
		>
			<button
				aria-controls={listboxId}
				aria-describedby={getDescribedBy(descriptionId, errorId)}
				aria-expanded={open}
				aria-haspopup="listbox"
				aria-invalid={Boolean(error) || undefined}
				aria-label={`${label}: ${selectedLabel ?? placeholder}`}
				className={dropdownControlClassName}
				disabled={disabled}
				id={fieldId}
				onClick={() => (open ? closeList() : openList())}
				onKeyDown={handleTriggerKeyDown}
				ref={triggerRef}
				style={getAnchorStyle(anchorName)}
				type="button"
			>
				<span className={cn(!selectedLabel && "text-muted-foreground")}>
					{selectedLabel ?? placeholder}
				</span>
				<DropdownCaret open={open} />
			</button>

			<div
				className="dropdown-popover z-50 mt-1"
				onToggle={handlePopoverToggle}
				popover="auto"
				ref={popoverRef}
				style={getAnchorStyle(anchorName)}
			>
				<div
					aria-activedescendant={getActiveDescendantId(fieldId, activeIndex)}
					aria-labelledby={`${fieldId}-label`}
					className={dropdownPanelClassName}
					id={listboxId}
					onKeyDown={(event) =>
						handleListNavigation({
							activeIndex,
							close: closeList,
							event,
							onSelectActive: () => selectOption(activeIndex),
							options,
							setActiveIndex,
						})
					}
					ref={listboxRef}
					role="listbox"
					tabIndex={-1}
				>
					{options.map((option, index) => (
						<button
							aria-disabled={option.disabled || undefined}
							aria-selected={option.value === value}
							className={cn(
								optionBaseClassName,
								"border-b last:border-b-0",
								option.value === value && "text-primary",
							)}
							data-active={activeIndex === index}
							data-disabled={option.disabled || undefined}
							disabled={option.disabled}
							id={`${fieldId}-option-${index}`}
							key={option.value}
							onClick={() => selectOption(index)}
							onMouseDown={(event) => event.preventDefault()}
							onMouseEnter={() => !option.disabled && setActiveIndex(index)}
							role="option"
							type="button"
						>
							<span className="grid grid-cols-[1rem_1fr] gap-3">
								<span aria-hidden="true" className="font-mono text-primary">
									{option.value === value ? "✓" : ""}
								</span>
								<span className="grid gap-1">
									{renderOptionContent(option)}
								</span>
							</span>
						</button>
					))}
				</div>
			</div>
		</FieldShell>
	);
}
