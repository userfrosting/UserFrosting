// TODO Remove pending merging of https://github.com/gulpjs/gulplog/pull/12
declare module "gulplog" {
    /**
     * Highest log level. Typically used for debugging purposes.
     *
     * If the first argument is a string, all arguments are passed to node's util.format() before being emitted.
     * @param msg Message to log
     * @param args Arguments to format message with via util.format()
     */
    export function debug(msg: string, ...args: any[]): void;
    export function debug(msg: any): void;
    /**
     * Standard log level. Typically used for user information.
     *
     * If the first argument is a string, all arguments are passed to node's util.format() before being emitted.
     * @param msg Message to log
     * @param args Arguments to format message with via util.format()
     */
    export function info(msg: string, ...args: any[]): void;
    export function info(msg: any): void;
    /**
     * Warning log level. Typically used for warnings.
     *
     * If the first argument is a string, all arguments are passed to node's util.format() before being emitted.
     * @param msg Message to log
     * @param args Arguments to format message with via util.format()
     */
    export function warn(msg: string, ...args: any[]): void;
    export function warn(msg: any): void;
    /**
     * Error log level. Typically used when things went horribly wrong.
     *
     * If the first argument is a string, all arguments are passed to node's util.format() before being emitted.
     * @param msg Message to log
     * @param args Arguments to format message with via util.format()
     */
    export function error(msg: string, ...args: any[]): void;
    export function error(msg: any): void;

    const _default = {
        debug,
        info,
        warn,
        error,
    };

    export default _default;
}

declare module "gulp-concat-css" {
    export = (...args?: unknown[]) => unknown;
}
