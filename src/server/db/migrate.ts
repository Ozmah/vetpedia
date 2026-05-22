import { migrate } from "drizzle-orm/libsql/migrator";
import { db, ensureDatabasePragmas } from "./client";

await ensureDatabasePragmas();
await migrate(db, { migrationsFolder: "./drizzle/migrations" });

console.log("SQLite migrations completed.");
