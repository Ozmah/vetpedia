import tailwindcss from "@tailwindcss/vite";
import { devtools } from "@tanstack/devtools-vite";

import { tanstackStart } from "@tanstack/react-start/plugin/vite";

import viteReact from "@vitejs/plugin-react";
import { nitro } from "nitro/vite";
import { defineConfig } from "vite";

const config = defineConfig({
	resolve: { tsconfigPaths: true },
	plugins: [
		devtools({
			injectSource: {
				enabled: true,
			},
			eventBusConfig: {
				port: 1234,
				debug: false,
				enabled: true,
			},
			editor: {
				name: "zed",
				open: async (path, lineNumber, columnNumber) => {
					const { exec } = await import("node:child_process");
					exec(
						`zed "${(path).replaceAll("$", "\\$")}${lineNumber ? `:${lineNumber}` : ""}${columnNumber ? `:${columnNumber}` : ""}"`,
					);
				},
			},
			removeDevtoolsOnBuild: true,
		}),
		tailwindcss(),
		tanstackStart(),
		nitro({
			preset: "bun",
		}),
		viteReact(),
	],
});

export default config;
