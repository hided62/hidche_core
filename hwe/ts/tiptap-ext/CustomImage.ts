//https://github.com/joevallender/tiptap2-image-example/blob/main/src/extensions/custom-image-3.js
import Image, { type ImageOptions } from '@tiptap/extension-image'
import { mergeAttributes } from '@tiptap/core'

export interface CustomImageOptions extends ImageOptions {
    sizes: string[],
}

declare module '@tiptap/core' {
    interface Commands<ReturnType> {
        setImage: (options: { src: string, alt?: string, title?: string, size: string }) => ReturnType,
    }
}

export default Image.extend<CustomImageOptions>({
    name: 'custom-image',

    addOptions() {
        return {
            ...Image.options,
            sizes: ['original', 'small', 'medium', 'large'],
        }
    },
    addAttributes() {
        return {
            src: {
                default: null,
            },
            alt: {
                default: null,
            },
            title: {
                default: null,
            },
            size: {
                default: 'original',
                rendered: false
            },
            align: {
                default: 'center',
                rendered: false
            }
        }
    },

    addCommands() {
        return {
            // This is unchanged from the original
            // Image setImage function
            // However, if I extended addComands in
            // the same way as addAttributes `this`
            // seemed to lose context, so I've just
            // copied it in here directly
            setImage: (options) => ({ tr, commands }) => {
                // eslint-disable-next-line @typescript-eslint/ban-ts-comment
                // @ts-ignore
                if (tr.selection?.node?.type?.name == 'custom-image') {
                    return commands.updateAttributes('custom-image', options)
                }
                else {
                    return commands.insertContent({
                        type: this.name,
                        attrs: options
                    })
                }
            },
        }
    },

    renderHTML({ node, HTMLAttributes }) {
        // When we render the HTML, grab the
        // size and add an appropriate
        // corresponding class

        HTMLAttributes.class = ' custom-image-' + node.attrs.size;
        HTMLAttributes.class += ' custom-image-align-' + node.attrs.align;
        return [
            'img',
            mergeAttributes(this.options.HTMLAttributes, HTMLAttributes)
        ]
    }
})