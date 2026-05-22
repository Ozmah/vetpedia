import "@tanstack/react-start/server-only";

import { mkdirSync } from "node:fs";
import { dirname, resolve } from "node:path";
import { createClient } from "@libsql/client";
import { drizzle } from "drizzle-orm/libsql";
import { getEnv } from "../../lib/env";
import * as schema from "./schema";

const databaseUrl = normalizeDatabaseUrl(
	getEnv(
		"DATABASE_URL",
		getEnv("DATABASE_PATH", "file:./data/vetpedia.sqlite"),
	),
);
const databasePath = fileUrlToPath(databaseUrl);

if (databasePath) {
	mkdirSync(dirname(databasePath), { recursive: true });
}

export const client = createClient({ url: databaseUrl });
export const db = drizzle({ client, schema });

let pragmasPromise: Promise<void> | null = null;

export type Db = typeof db;

export function ensureDatabasePragmas() {
	pragmasPromise ??= Promise.all([
		client.execute("PRAGMA foreign_keys = ON"),
		client.execute("PRAGMA journal_mode = WAL"),
		client.execute("PRAGMA synchronous = NORMAL"),
		client.execute("PRAGMA busy_timeout = 5000"),
	]).then(() => undefined);

	return pragmasPromise;
}

function normalizeDatabaseUrl(value: string) {
	if (value === ":memory:" || value.startsWith("file:")) return value;

	return `file:${value}`;
}

function fileUrlToPath(value: string) {
	if (!value.startsWith("file:") || value === "file::memory:") return null;

	return resolve(value.slice("file:".length));
}
