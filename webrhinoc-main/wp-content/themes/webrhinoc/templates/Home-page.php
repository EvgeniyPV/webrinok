<?php
// Template Name: Home Page

get_header();
?>
    <section class="hero">
        <img src="<?php the_field('hero_bg_pc'); ?>" alt="" class="hero-bg">
        <img src="<?php the_field('hero_bg_mob'); ?>" alt="" class="hero-bg-mob hero-bg">
        <div class="container">
            <div class="hero-title">
                <?php the_field('hero_title'); ?>
            </div>
        </div>
    </section>
    <section class="feedback">
        <div class="container">
            <div class="feedback__img">
                <img src="<?php the_field('feedback_image'); ?>" alt="">
            </div>
            <div class="feedback__text">
                <div class="feedback-title">
                    <?php the_field('feedback_title'); ?>
                </div>
                <?php while (have_rows('feedback_socials')): the_row() ?>
                    <div class="feedback__chooses feedback-socials">
                        <div class="feedback__chooses-title">
                            <?php the_sub_field('title'); ?>
                        </div>
                        <div class="feedback__chooses-select">
                            <div class="feedback__chooses-label">

                            </div>
                            <div class="feedback__chooses-items">
                                <?php while (have_rows('socials')): the_row() ?>
                                    <div class="feedback__chooses-item">
                                        <?php the_sub_field('social_name'); ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php while (have_rows('feedback_services')): the_row() ?>
                    <div class="feedback__chooses feedback-services">
                        <div class="feedback__chooses-title">
                            <?php the_sub_field('title'); ?>
                        </div>
                        <div class="feedback__chooses-select">
                            <div class="feedback__chooses-label">

                            </div>
                            <div class="feedback__chooses-items">
                                <?php while (have_rows('services')): the_row() ?>
                                    <div class="feedback__chooses-item">
                                        <?php the_sub_field('services_name'); ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php echo do_shortcode('[contact-form-7 id="64" title="Contact form 1"]') ?>
            </div>
        </div>
    </section>
<?php
get_footer();
