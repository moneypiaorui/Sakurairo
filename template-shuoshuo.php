<?php
/**
 * Template Name: 说说时间轴
 * Template Post Type: page
 *
 * Display published Shuoshuo entries on a normal WordPress page.
 *
 * @package Sakurairo
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div id="primary" class="content-area sakurairo-shuoshuo-page">
    <main id="main" class="site-main" role="main">
        <?php while (have_posts()) : the_post(); ?>
            <?php get_template_part('tpl/content', 'page'); ?>

            <?php if (!post_password_required()) : ?>
                <?php
                // A separate parameter avoids conflicts with page-content pagination
                // and works when this template is used as the static front page.
                $shuoshuo_page = isset($_GET['ss_page']) && is_scalar($_GET['ss_page'])
                    ? max(1, absint(wp_unslash($_GET['ss_page'])))
                    : 1;
                $shuoshuo_page_url = get_permalink();
                $shuoshuo_query = new WP_Query(array(
                    'post_type'           => 'shuoshuo',
                    'post_status'         => 'publish',
                    'has_password'        => false,
                    'posts_per_page'      => 20,
                    'paged'               => $shuoshuo_page,
                    'orderby'             => array('date' => 'DESC', 'ID' => 'DESC'),
                    'ignore_sticky_posts' => true,
                ));
                $shuoshuo_month = '';
                ?>

                <section class="sakurairo-shuoshuo-feed" aria-label="<?php echo esc_attr__('Shuoshuo', 'sakurairo'); ?>">
                    <p class="sakurairo-shuoshuo-count">
                        <?php echo esc_html(sprintf('共 %s 条说说', number_format_i18n($shuoshuo_query->found_posts))); ?>
                    </p>

                    <?php if ($shuoshuo_query->have_posts()) : ?>
                        <div id="shuoshuo-timeline" class="sakurairo-shuoshuo-timeline">
                            <?php while ($shuoshuo_query->have_posts()) : $shuoshuo_query->the_post(); ?>
                                <?php
                                $entry_month = get_the_date('Y-m');
                                if ($entry_month !== $shuoshuo_month) :
                                    $shuoshuo_month = $entry_month;
                                    ?>
                                    <h2 class="sakurairo-shuoshuo-month"><?php echo esc_html(get_the_date('Y年n月')); ?></h2>
                                <?php endif; ?>

                                <article id="shuoshuo-<?php echo esc_attr(get_the_ID()); ?>" class="sakurairo-shuoshuo-card">
                                    <header class="sakurairo-shuoshuo-meta">
                                        <?php echo get_avatar(get_the_author_meta('ID'), 40, '', get_the_author(), array('class' => 'sakurairo-shuoshuo-avatar')); ?>
                                        <div>
                                            <span class="sakurairo-shuoshuo-author"><?php echo esc_html(get_the_author()); ?></span>
                                            <a class="sakurairo-shuoshuo-date" href="#shuoshuo-<?php echo esc_attr(get_the_ID()); ?>">
                                                <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date('n月j日 H:i')); ?></time>
                                            </a>
                                        </div>
                                    </header>
                                    <div class="sakurairo-shuoshuo-body">
                                        <?php the_content(); ?>
                                    </div>
                                </article>
                            <?php endwhile; ?>
                        </div>

                        <?php if ($shuoshuo_query->max_num_pages > 1) : ?>
                            <?php
                            $placeholder = 999999999;
                            $pagination = paginate_links(array(
                                'base'         => str_replace((string) $placeholder, '%#%', add_query_arg('ss_page', $placeholder, $shuoshuo_page_url)),
                                'format'       => '',
                                'current'      => $shuoshuo_page,
                                'total'        => $shuoshuo_query->max_num_pages,
                                'prev_text'    => '较新的说说',
                                'next_text'    => '更早的说说',
                                'type'         => 'list',
                                'add_args'     => false,
                                'add_fragment' => '#shuoshuo-timeline',
                            ));
                            ?>
                            <nav class="sakurairo-shuoshuo-pagination" aria-label="说说分页">
                                <?php echo wp_kses_post($pagination); ?>
                            </nav>
                        <?php endif; ?>
                    <?php else : ?>
                        <p class="sakurairo-shuoshuo-empty">
                            <?php echo esc_html($shuoshuo_page > 1 ? '这一页没有说说了。' : '还没有说说，来记录第一份日常吧。'); ?>
                        </p>
                        <?php if ($shuoshuo_page > 1) : ?>
                            <p class="sakurairo-shuoshuo-count"><a href="<?php echo esc_url($shuoshuo_page_url); ?>">返回第一页</a></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>
                <?php wp_reset_postdata(); ?>
            <?php endif; ?>
        <?php endwhile; ?>
    </main>
</div>

<?php // Keep styles with the template so they also load during PJAX navigation. ?>
<style>
.sakurairo-shuoshuo-page {
    --ss-card: rgba(255, 255, 255, .88);
    --ss-text: #444;
    --ss-muted: #767676;
    --ss-border: rgba(125, 125, 125, .16);
    --ss-accent: var(--theme-skin, #667eea);
}
body.dark .sakurairo-shuoshuo-page {
    --ss-card: var(--dark-bg-secondary, rgba(26, 26, 26, .88));
    --ss-text: var(--dark-text-primary, #ccc);
    --ss-muted: #aaa;
    --ss-border: var(--dark-border-color, rgba(125, 125, 125, .2));
    --ss-accent: var(--theme-skin-dark, #a5b4fc);
}
.sakurairo-shuoshuo-feed { max-width: 720px; margin: 0 auto 40px; padding: 0 20px; }
.sakurairo-shuoshuo-count, .sakurairo-shuoshuo-empty { text-align: center; color: var(--ss-muted); }
.sakurairo-shuoshuo-count { margin: 20px 0 28px; font-size: 14px; }
.sakurairo-shuoshuo-empty { padding: 48px 0; }
.sakurairo-shuoshuo-month { margin: 32px 0 20px; text-align: center; color: var(--ss-accent); font-size: 18px; }
.sakurairo-shuoshuo-card {
    margin-bottom: 20px;
    padding: 20px;
    border: 1px solid var(--ss-border);
    border-radius: 12px;
    background: var(--ss-card);
    color: var(--ss-text);
    box-shadow: 0 2px 12px rgba(0, 0, 0, .05);
    scroll-margin-top: 90px;
}
.sakurairo-shuoshuo-meta { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
.sakurairo-shuoshuo-meta .avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.sakurairo-shuoshuo-author { display: block; font-size: 14px; font-weight: 600; }
.sakurairo-shuoshuo-date { display: block; color: var(--ss-muted); font-size: 12px; }
.sakurairo-shuoshuo-body { font-size: 15px; line-height: 1.8; overflow-wrap: anywhere; }
.sakurairo-shuoshuo-body > :first-child { margin-top: 0; }
.sakurairo-shuoshuo-body > :last-child { margin-bottom: 0; }
.sakurairo-shuoshuo-body img, .sakurairo-shuoshuo-body video { max-width: 100%; height: auto; border-radius: 8px; }
.sakurairo-shuoshuo-body iframe { max-width: 100%; }
.sakurairo-shuoshuo-body figure { max-width: 100%; margin: 12px 0; }
.sakurairo-shuoshuo-body pre { max-width: 100%; overflow-x: auto; }
.sakurairo-shuoshuo-page .sakurairo-shuoshuo-pagination ul.page-numbers {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    width: 100%;
    margin: 28px 0;
    padding: 0;
    list-style: none;
}
.sakurairo-shuoshuo-page .sakurairo-shuoshuo-pagination ul.page-numbers > li {
    display: flex;
    flex: 0 0 auto;
    margin: 0;
    padding: 0;
    list-style: none;
}
/* Reset the theme's 35px prev/next buttons and comment-pagination spacing. */
.sakurairo-shuoshuo-page .sakurairo-shuoshuo-pagination a.page-numbers,
.sakurairo-shuoshuo-page .sakurairo-shuoshuo-pagination span.page-numbers {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    position: static;
    float: none;
    width: auto;
    height: auto;
    min-width: 44px;
    min-height: 44px;
    margin: 0;
    padding: 10px 14px;
    border: 1px solid var(--ss-border);
    border-radius: 8px;
    background: var(--ss-card);
    color: var(--ss-text);
    font-size: 14px;
    line-height: 1.4;
    white-space: nowrap;
    text-decoration: none;
}
.sakurairo-shuoshuo-page .sakurairo-shuoshuo-pagination span.page-numbers.current {
    border-color: var(--ss-accent);
    box-shadow: inset 0 0 0 1px var(--ss-accent);
    font-weight: 700;
}
.sakurairo-shuoshuo-pagination a::after { content: none; }
.sakurairo-shuoshuo-pagination a:focus-visible, .sakurairo-shuoshuo-date:focus-visible { outline: 2px solid var(--ss-accent); outline-offset: 3px; }
#shuoshuo-timeline { scroll-margin-top: 90px; }
@media (max-width: 600px) {
    .sakurairo-shuoshuo-feed { padding: 0 12px; }
    .sakurairo-shuoshuo-card { padding: 16px; }
}
</style>

<?php get_footer(); ?>
