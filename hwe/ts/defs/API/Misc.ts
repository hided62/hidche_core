import type { ValidResponse } from "@/defs";

export type UploadImageResponse = ValidResponse & {
    path: string;
}