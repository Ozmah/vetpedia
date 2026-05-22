import { defineConfig } from "drizzle-kit";

export default defineConfig({
	dialect: "sqlite",
	schema: "./src/server/db/schema.ts",
	out: "./drizzle/migrations",
	dbCredentials: {
		url:
			process.env.DATABASE_URL ??
			process.env.DATABASE_PATH ??
			"file:./data/vetpedia.sqlite",
	},
	strict: true,
	verbose: true,
});
