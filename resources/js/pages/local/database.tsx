import { Head, Link } from '@inertiajs/react';
import { Database } from 'lucide-react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { index, show } from '@/routes/local/database';
import type { BreadcrumbItem } from '@/types';

type TableSummary = {
    name: string;
    schema: string | null;
    column_count: number;
    record_count: number;
};

type TableColumn = {
    name: string;
    type: string;
    nullable: boolean;
    default: string | number | boolean | null;
    auto_increment: boolean;
};

type TableIndex = {
    name: string;
    columns: string[];
    unique: boolean;
    primary: boolean;
};

type ForeignKey = {
    name: string;
    columns: string[];
    foreign_table: string | null;
    foreign_columns: string[];
};

type Records = {
    data: Record<string, string | number | boolean | null>[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    next_page_url: string | null;
    prev_page_url: string | null;
};

type SelectedTable = {
    name: string;
    columns: TableColumn[];
    indexes: TableIndex[];
    foreign_keys: ForeignKey[];
    records: Records;
    redacted_columns: string[];
};

type Props = {
    tables: TableSummary[];
    selectedTable: SelectedTable | null;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Local database',
        href: index(),
    },
];

export default function LocalDatabase({ tables, selectedTable }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Local database" />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <header className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="space-y-1">
                        <div className="flex items-center gap-2">
                            <Database className="size-5 text-muted-foreground" />
                            <h1 className="text-2xl font-semibold tracking-tight">
                                Local database
                            </h1>
                            <Badge variant="secondary">read-only</Badge>
                        </div>
                        <p className="text-sm text-muted-foreground">
                            Local-only database inspection for development.
                            Sensitive values are redacted.
                        </p>
                    </div>
                </header>

                <div className="grid gap-4 lg:grid-cols-[18rem_minmax(0,1fr)]">
                    <Card className="h-fit">
                        <CardHeader>
                            <CardTitle>Tables</CardTitle>
                            <CardDescription>
                                {tables.length} available tables
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-2">
                            {tables.map((table) => (
                                <Button
                                    key={table.name}
                                    asChild
                                    variant={
                                        selectedTable?.name === table.name
                                            ? 'secondary'
                                            : 'ghost'
                                    }
                                    className="h-auto justify-start px-3 py-2"
                                >
                                    <Link
                                        href={show({ table: table.name })}
                                        prefetch
                                    >
                                        <span className="flex min-w-0 flex-1 flex-col items-start gap-1">
                                            <span className="truncate font-medium">
                                                {table.name}
                                            </span>
                                            <span className="text-xs text-muted-foreground">
                                                {table.column_count} columns ·{' '}
                                                {table.record_count} rows
                                            </span>
                                        </span>
                                    </Link>
                                </Button>
                            ))}
                        </CardContent>
                    </Card>

                    {selectedTable ? (
                        <TableDetails table={selectedTable} />
                    ) : (
                        <Card>
                            <CardHeader>
                                <CardTitle>Select a table</CardTitle>
                                <CardDescription>
                                    Choose a table to inspect its schema and
                                    records.
                                </CardDescription>
                            </CardHeader>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

function TableDetails({ table }: { table: SelectedTable }) {
    const [activeTab, setActiveTab] = useState<'schema' | 'records'>('schema');

    return (
        <div className="flex min-w-0 flex-col gap-4">
            <Card>
                <CardHeader>
                    <div className="flex flex-wrap items-center gap-2">
                        <CardTitle>{table.name}</CardTitle>
                        <Badge variant="outline">
                            {table.records.total} records
                        </Badge>
                    </div>
                    <CardDescription>
                        Schema, indexes, foreign keys, and paginated records.
                    </CardDescription>
                </CardHeader>
            </Card>

            <div className="flex gap-2" role="tablist" aria-label="Table views">
                <Button
                    type="button"
                    variant={activeTab === 'schema' ? 'secondary' : 'outline'}
                    role="tab"
                    aria-selected={activeTab === 'schema'}
                    onClick={() => setActiveTab('schema')}
                >
                    Schema
                </Button>
                <Button
                    type="button"
                    variant={activeTab === 'records' ? 'secondary' : 'outline'}
                    role="tab"
                    aria-selected={activeTab === 'records'}
                    onClick={() => setActiveTab('records')}
                >
                    Records
                </Button>
            </div>

            {activeTab === 'schema' ? (
                <SchemaPanel table={table} />
            ) : (
                <RecordsPanel table={table} />
            )}
        </div>
    );
}

function SchemaPanel({ table }: { table: SelectedTable }) {
    return (
        <>
            <Card>
                <CardHeader>
                    <CardTitle>Schema</CardTitle>
                    <CardDescription>
                        Column metadata reported by Laravel Schema.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="overflow-x-auto rounded-md border">
                        <table className="w-full text-sm">
                            <thead className="bg-muted/50 text-left">
                                <tr>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Nullable</TableHead>
                                    <TableHead>Default</TableHead>
                                    <TableHead>Flags</TableHead>
                                </tr>
                            </thead>
                            <tbody>
                                {table.columns.map((column) => (
                                    <tr key={column.name} className="border-t">
                                        <TableCell className="font-mono">
                                            {column.name}
                                        </TableCell>
                                        <TableCell>{column.type}</TableCell>
                                        <TableCell>
                                            {column.nullable ? 'yes' : 'no'}
                                        </TableCell>
                                        <TableCell>
                                            {formatValue(column.default)}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-wrap gap-1">
                                                {column.auto_increment && (
                                                    <Badge variant="outline">
                                                        auto increment
                                                    </Badge>
                                                )}
                                                {table.redacted_columns.includes(
                                                    column.name,
                                                ) && (
                                                    <Badge variant="secondary">
                                                        redacted
                                                    </Badge>
                                                )}
                                            </div>
                                        </TableCell>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <div className="grid gap-4 xl:grid-cols-2">
                <MetadataCard title="Indexes" items={table.indexes} />
                <MetadataCard title="Foreign keys" items={table.foreign_keys} />
            </div>
        </>
    );
}

function RecordsPanel({ table }: { table: SelectedTable }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Records</CardTitle>
                <CardDescription>
                    Showing {table.records.from ?? 0} to {table.records.to ?? 0}{' '}
                    of {table.records.total} records.
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                <div className="overflow-x-auto rounded-md border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left">
                            <tr>
                                {table.columns.map((column) => (
                                    <TableHead key={column.name}>
                                        {column.name}
                                    </TableHead>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {table.records.data.map((record, index) => (
                                <tr key={index} className="border-t">
                                    {table.columns.map((column) => (
                                        <TableCell
                                            key={column.name}
                                            className="max-w-72 truncate font-mono"
                                        >
                                            {formatValue(record[column.name])}
                                        </TableCell>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <div className="flex items-center justify-between gap-3">
                    <p className="text-sm text-muted-foreground">
                        Page {table.records.current_page} of{' '}
                        {table.records.last_page}
                    </p>
                    <div className="flex gap-2">
                        {table.records.prev_page_url ? (
                            <Button asChild variant="outline" size="sm">
                                <Link href={table.records.prev_page_url}>
                                    Previous
                                </Link>
                            </Button>
                        ) : (
                            <Button variant="outline" size="sm" disabled>
                                Previous
                            </Button>
                        )}
                        {table.records.next_page_url ? (
                            <Button asChild variant="outline" size="sm">
                                <Link href={table.records.next_page_url}>
                                    Next
                                </Link>
                            </Button>
                        ) : (
                            <Button variant="outline" size="sm" disabled>
                                Next
                            </Button>
                        )}
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

function MetadataCard({
    title,
    items,
}: {
    title: string;
    items: (TableIndex | ForeignKey)[];
}) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>{title}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
                {items.length === 0 ? (
                    <p className="text-sm text-muted-foreground">None.</p>
                ) : (
                    items.map((item, index) => (
                        <div
                            key={index}
                            className="rounded-md border p-3 text-sm"
                        >
                            <div className="font-mono font-medium">
                                {item.name || 'unnamed'}
                            </div>
                            <div className="mt-1 text-muted-foreground">
                                {metadataDescription(item)}
                            </div>
                        </div>
                    ))
                )}
            </CardContent>
        </Card>
    );
}

function metadataDescription(item: TableIndex | ForeignKey) {
    if ('foreign_table' in item) {
        return `${item.columns.join(', ')} → ${item.foreign_table ?? 'unknown'}(${item.foreign_columns.join(', ')})`;
    }

    const flags = [
        item.primary ? 'primary' : null,
        item.unique ? 'unique' : null,
    ]
        .filter(Boolean)
        .join(', ');

    return `${item.columns.join(', ')}${flags ? ` · ${flags}` : ''}`;
}

function TableHead({ children }: { children: ReactNode }) {
    return <th className="px-3 py-2 font-medium">{children}</th>;
}

function TableCell({
    children,
    className = '',
}: {
    children: ReactNode;
    className?: string;
}) {
    return <td className={`px-3 py-2 align-top ${className}`}>{children}</td>;
}

function formatValue(value: string | number | boolean | null | undefined) {
    if (value === null || value === undefined) {
        return <span className="text-muted-foreground">null</span>;
    }

    if (typeof value === 'boolean') {
        return value ? 'true' : 'false';
    }

    return String(value);
}
