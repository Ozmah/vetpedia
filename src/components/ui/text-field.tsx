import type {
	InputHTMLAttributes,
	ReactNode,
	TextareaHTMLAttributes,
} from "react";
import { useId } from "react";
import { cn } from "../../lib/cn";

type TextInputProps = Omit<
	InputHTMLAttributes<HTMLInputElement>,
	"children"
> & {
	description?: string;
	error?: string;
	label: string;
};

type TextAreaProps = Omit<
	TextareaHTMLAttributes<HTMLTextAreaElement>,
	"children"
> & {
	description?: string;
	error?: string;
	label: string;
};

export function TextInput({
	className,
	description,
	error,
	id,
	label,
	type = "text",
	...props
}: TextInputProps) {
	const generatedId = useId();
	const inputId = id ?? generatedId;
	const descriptionId = description ? `${inputId}-description` : undefined;
	const errorId = error ? `${inputId}-error` : undefined;
	const describedBy = [descriptionId, errorId, props["aria-describedby"]]
		.filter(Boolean)
		.join(" ");

	return (
		<FieldShell
			description={description}
			descriptionId={descriptionId}
			error={error}
			errorId={errorId}
			id={inputId}
			label={label}
		>
			<input
				aria-describedby={describedBy || undefined}
				aria-invalid={Boolean(error) || undefined}
				className={cn(fieldControlClassName, className)}
				id={inputId}
				type={type}
				{...props}
			/>
		</FieldShell>
	);
}

export function TextArea({
	className,
	description,
	error,
	id,
	label,
	...props
}: TextAreaProps) {
	const generatedId = useId();
	const textareaId = id ?? generatedId;
	const descriptionId = description ? `${textareaId}-description` : undefined;
	const errorId = error ? `${textareaId}-error` : undefined;
	const describedBy = [descriptionId, errorId, props["aria-describedby"]]
		.filter(Boolean)
		.join(" ");

	return (
		<FieldShell
			description={description}
			descriptionId={descriptionId}
			error={error}
			errorId={errorId}
			id={textareaId}
			label={label}
		>
			<textarea
				aria-describedby={describedBy || undefined}
				aria-invalid={Boolean(error) || undefined}
				className={cn(
					fieldControlClassName,
					"min-h-32 resize-y py-3",
					className,
				)}
				id={textareaId}
				{...props}
			/>
		</FieldShell>
	);
}

export function FieldShell({
	children,
	description,
	descriptionId,
	error,
	errorId,
	id,
	label,
	reserveMetaSpace = true,
}: {
	children: ReactNode;
	description?: string;
	descriptionId?: string;
	error?: string;
	errorId?: string;
	id: string;
	label: string;
	reserveMetaSpace?: boolean;
}) {
	return (
		<div className="grid w-full gap-2">
			<label
				className="font-mono font-semibold text-[0.72rem] text-primary uppercase leading-tight tracking-[0.14em]"
				htmlFor={id}
				id={`${id}-label`}
			>
				{label}
			</label>
			{children}
			<div className={reserveMetaSpace ? "min-h-5" : undefined}>
				{description ? (
					<p
						className="m-0 text-muted-foreground text-xs leading-5"
						id={descriptionId}
					>
						{description}
					</p>
				) : null}
				{error ? (
					<p className="m-0 text-destructive text-xs leading-5" id={errorId}>
						{error}
					</p>
				) : null}
			</div>
		</div>
	);
}

export const fieldControlClassName =
	"min-h-12 w-full rounded-[0.9rem] border border-input bg-card/70 px-4 text-[16px] leading-6 text-foreground outline-none transition-[background-color,border-color,box-shadow,color] duration-150 ease-out placeholder:text-muted-foreground/70 focus-visible:border-primary focus-visible:bg-card focus-visible:shadow-[0_0_0_4px_var(--primary-wash)] disabled:cursor-not-allowed disabled:bg-muted/60 disabled:text-muted-foreground aria-invalid:border-destructive/60 aria-invalid:shadow-[0_0_0_4px_oklch(0.45_0.105_24/0.12)]";
