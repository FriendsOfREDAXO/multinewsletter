<!DOCTYPE html>
<?= '<html lang="'. rex_clang::getCurrent()->getCode() .'">' ?>
<head>
	<meta charset="utf-8" />
	<base href="<?= \rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : \rex::getServer() ?>" />
<?php
    if (\rex_addon::get('yrewrite')->isAvailable()) {
        $yrewrite = new \rex_yrewrite_seo();
        echo $yrewrite->getRobotsTag();
        echo $yrewrite->getTitleTag();
    }
    if (file_exists(rex_path::media('favicon.ico'))) {
        echo '<link rel="icon" href="'. rex_url::media('favicon.ico') .'">';
    }
?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style type="text/css">
		body {
			font-family: Arial, sans-serif;
			margin: 0px;
			width: 100%;
		}
		header {
			background-color: #bdbcbc;
			color: white;
		}
		header .onlinelink a, footer .footer-box a {
			color: white;
			text-decoration: none;
		}
		footer {
		    background-color: #7b7979;
		}
		footer .footer-box {
			background-color: #a49f9f;
			float: left;
			margin: 0 1em 1em 0;
			padding: 0.5em 1em;
		}
		header, footer, section {
			margin: 0 auto;
			padding: 1em;
		}
		h1.h1 {
			background-color: #fdb813;
			color: white;
			padding: 0.5em;
		}
		img {
			max-width: 100%;
		}
		.container {
			margin: 0 auto;
			max-width: 1000px;
		}
		.col-12 {
			width: 100%;
		}
		div.col-md-8, div.col-md-6, div.col-md-4 {
			float: left;
		}
		@media screen and (max-width: 700px) {
			div.col-md-8, div.col-md-6, div.col-md-4 {
				width: 100%;
			}
		}
		@media screen and (min-width: 701px) {
			div.col-md-8 {
				width: 66%
			}
			div.col-md-6 {
				width: 50%
			}
			div.col-md-4 {
				width: 34%
			}
		}
	</style>
</head>

<body class="newsletter prevent_d2u_helper_styles">
	<header>
		<center>
			<?php
            if ('' !== rex_config::get('d2u_helper', 'template_logo', '')) {
                echo '<img class="logo" src="'. rex_url::media(rex_config::get('d2u_helper', 'template_logo')) .'" alt="Logo">';
            }
            ?>
			<p class="onlinelink"><a href="+++NEWSLETTERLINK+++">Wenn dieser
				Newsletter nicht korrekt angezeigt wird, klicken Sie bitte hier</a>.</p>
		</center>
	</header>
	<br clear="all">
	<section class="section">
		<div class="container">
			<div class="row">
				REX_ARTICLE[]
			</div>
			<div class="row">
				<div class="col-xs-12"><br></div>
			</div>
		</div>
		<br clear="all">
	</section>
	<footer>
		<div class="container">
			<div class="row">
				<?php
                    $impressum = rex_article::get(rex_config::get('d2u_helper', 'article_id_impress'));
                    if ($impressum instanceof rex_article) {
                        echo '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">';
                        echo '<div class="footer-box"><a href="'. $impressum->getUrl() .'">'. $impressum->getName() .'</a></div>';
                        echo '</div>';
                    }

                    echo '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">';
                    echo '<div class="footer-box"><a href="+++ABMELDELINK+++">Newsletter abmelden</a></div>';
                    echo '</div>';
                ?>
			</div>
		</div>
		<br clear="all">
	</footer>
</body>
</html>