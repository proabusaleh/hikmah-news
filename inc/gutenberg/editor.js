/**
 * Hikmah News Blocks — Editor Scripts
 * @package HikmahNews
 */
(function(wp) {
    const { registerBlockType } = wp.blocks;
    const { useBlockProps, InspectorControls } = wp.blockEditor;
    const { PanelBody, SelectControl, RangeControl, ToggleControl, TextControl, ColorPicker } = wp.components;
    const { createElement: el, Fragment } = wp.element;
    const ServerSideRender = wp.serverSideRender;
    const { __ } = wp.i18n;

    const categoryOptions = (window.hikmahnewsBlockData?.categories || []).map(c => ({
        value: c.value,
        label: c.label,
    }));
    categoryOptions.unshift({ value: '', label: 'All Categories' });

    // Shared Inspector Controls
    function NewsBlockControls({ attributes, setAttributes, showCategory, showCount, showLayout }) {
        return el(InspectorControls, null,
            el(PanelBody, { title: __('Content Settings', 'hikmahnews'), initialOpen: true },
                showCount !== false && el(RangeControl, {
                    label: __('Number of Posts', 'hikmahnews'),
                    value: attributes.postCount || 6,
                    onChange: v => setAttributes({ postCount: v }),
                    min: 1, max: 12,
                }),
                showCategory !== false && el(SelectControl, {
                    label: __('Category', 'hikmahnews'),
                    value: attributes.category || '',
                    options: categoryOptions,
                    onChange: v => setAttributes({ category: v }),
                })
            ),
            showLayout !== false && el(PanelBody, { title: __('Layout', 'hikmahnews'), initialOpen: false },
                el(SelectControl, {
                    label: __('Columns', 'hikmahnews'),
                    value: attributes.columns || 3,
                    options: [
                        { value: 2, label: '2 Columns' },
                        { value: 3, label: '3 Columns' },
                        { value: 4, label: '4 Columns' },
                    ],
                    onChange: v => setAttributes({ columns: parseInt(v) }),
                }),
                el(ToggleControl, {
                    label: __('Show Excerpt', 'hikmahnews'),
                    checked: attributes.showExcerpt !== false,
                    onChange: v => setAttributes({ showExcerpt: v }),
                }),
                el(ToggleControl, {
                    label: __('Show Views', 'hikmahnews'),
                    checked: attributes.showViews !== false,
                    onChange: v => setAttributes({ showViews: v }),
                })
            )
        );
    }

    // Placeholder for editor preview
    function BlockPlaceholder({ icon, title, desc, columns }) {
        const cols = columns || 3;
        return el('div', { className: 'hikmahnews-block-preview' },
            el('div', { className: 'hikmahnews-block-preview__icon' }, icon),
            el('div', { className: 'hikmahnews-block-preview__title' }, title),
            el('div', { className: 'hikmahnews-block-preview__desc' }, desc),
            el('div', { className: `hikmahnews-editor-grid hikmahnews-editor-grid--${cols}`, style: { marginTop: 16 } },
                Array.from({ length: cols }, (_, i) =>
                    el('div', { className: 'hikmahnews-editor-card', key: i },
                        el('div', { className: 'hikmahnews-editor-card__img' }),
                        el('div', { className: 'hikmahnews-editor-card__body' },
                            el('div', { className: 'hikmahnews-editor-card__title' }),
                            el('div', { className: 'hikmahnews-editor-card__meta' })
                        )
                    )
                )
            )
        );
    }

    // Register all blocks dynamically
    const blocks = [
        { name: 'featured-news',  icon: '⭐', title: 'Featured News',  desc: 'Display featured/starred articles' },
        { name: 'latest-news',    icon: '🕒', title: 'Latest News',    desc: 'Show most recent articles' },
        { name: 'popular-news',   icon: '👁', title: 'Popular News',   desc: 'Most viewed articles' },
        { name: 'trending-news',  icon: '🔥', title: 'Trending News',  desc: 'Algorithm-based trending stories' },
        { name: 'category-news',  icon: '📂', title: 'Category News',  desc: 'Posts from a specific category' },
        { name: 'news-slider',    icon: '🎠', title: 'News Slider',    desc: 'Horizontal scrolling news carousel' },
        { name: 'news-grid',      icon: '📰', title: 'News Grid',      desc: 'Responsive news card grid' },
        { name: 'news-list',      icon: '📋', title: 'News List',      desc: 'Compact list-style news' },
        { name: 'video-news',     icon: '🎬', title: 'Video News',     desc: 'Video news with play buttons' },
        { name: 'breaking-news',  icon: '🔴', title: 'Breaking News',  desc: 'Breaking news ticker/alert' },
        { name: 'advertisement',  icon: '💰', title: 'Advertisement',  desc: 'Ad placement block' },
    ];

    blocks.forEach(block => {
        registerBlockType(`hikmahnews/${block.name}`, {
            apiVersion: 3,
            title: block.title,
            description: block.desc,
            category: 'hikmahnews-blocks',
            icon: block.icon,
            supports: {
                html: false,
                align: ['wide', 'full'],
                spacing: { margin: true, padding: true },
            },
            attributes: {
                postCount:   { type: 'number', default: 6 },
                category:    { type: 'string', default: '' },
                columns:     { type: 'number', default: 3 },
                showExcerpt: { type: 'boolean', default: true },
                showViews:   { type: 'boolean', default: true },
                title:       { type: 'string', default: block.title },
                adPosition:  { type: 'string', default: 'homepage_top' },
            },
            edit: function(props) {
                const { attributes, setAttributes, name } = props;
                const blockProps = useBlockProps({
                    className: `hikmahnews-block hikmahnews-block--${block.name}`,
                });

                // Use SSR for live preview
                return el(Fragment, null,
                    el(NewsBlockControls, {
                        attributes,
                        setAttributes,
                        showCategory: block.name !== 'breaking-news' && block.name !== 'advertisement',
                        showCount: block.name !== 'breaking-news' && block.name !== 'advertisement',
                        showLayout: !['breaking-news', 'advertisement', 'news-slider'].includes(block.name),
                    }),
                    el('div', blockProps,
                        el(ServerSideRender, {
                            block: name,
                            attributes: attributes,
                            LoadingResponsePlaceholder: () =>
                                el(BlockPlaceholder, {
                                    icon: block.icon,
                                    title: block.title,
                                    desc: 'Loading preview...',
                                    columns: attributes.columns,
                                }),
                            EmptyResponsePlaceholder: () =>
                                el(BlockPlaceholder, {
                                    icon: block.icon,
                                    title: block.title,
                                    desc: block.desc,
                                    columns: attributes.columns,
                                }),
                        })
                    )
                );
            },
            save: function() {
                return null; // Server-side rendered
            },
        });
    });

})(window.wp);