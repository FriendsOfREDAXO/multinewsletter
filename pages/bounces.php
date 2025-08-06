<?php

use FriendsOfRedaxo\MultiNewsletter\BounceHandler;

// Handle manual bounce processing
if (filter_input(INPUT_POST, 'process_bounces')) {
    try {
        $bounceHandler = new BounceHandler();
        $results = $bounceHandler->processBounces();
        
        if ($results['processed'] > 0) {
            echo rex_view::success(
                rex_i18n::msg('multinewsletter_bounce_processing_success', 
                    $results['processed'],
                    $results['hard_bounces'], 
                    $results['soft_bounces'],
                    $results['spam_complaints']
                )
            );
        } else {
            echo rex_view::info(rex_i18n::msg('multinewsletter_bounce_no_emails'));
        }
        
        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $error) {
                echo rex_view::warning($error);
            }
        }
        
    } catch (Exception $e) {
        echo rex_view::error('Error: ' . $e->getMessage());
    }
}

// Handle IMAP connection test
if (filter_input(INPUT_POST, 'test_imap_connection')) {
    try {
        // Create temporary bounce handler with POST data
        $bounceHandler = new BounceHandler();
        // Test with current config or form data
        if ($bounceHandler->testConnection()) {
            echo 'success';
        } else {
            echo 'Connection failed';
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
    exit; // AJAX response
}

?>

<div class="row">
    <div class="col-lg-6">
        <div class="rex-addon-output">
            <div class="panel panel-edit">
                <header class="panel-heading">
                    <div class="panel-title">
                        <?= rex_i18n::msg('multinewsletter_bounce_management') ?>
                    </div>
                </header>
                <div class="panel-body">
                    <p><?= rex_i18n::msg('multinewsletter_bounce_management_description') ?></p>
                    
                    <?php if (rex_addon::get('multinewsletter')->getConfig('bounce_enabled') === 'active'): ?>
                        <form action="<?= rex_url::currentBackendPage() ?>" method="post">
                            <button type="submit" name="process_bounces" class="btn btn-primary">
                                <i class="rex-icon rex-icon-refresh"></i>
                                <?= rex_i18n::msg('multinewsletter_process_bounces_manually') ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <?= rex_i18n::msg('multinewsletter_bounce_management_disabled') ?>
                            <a href="<?= rex_url::backendPage('multinewsletter/settings') ?>" class="btn btn-xs btn-primary">
                                <?= rex_i18n::msg('multinewsletter_enable_bounce_management') ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="rex-addon-output">
            <div class="panel panel-edit">
                <header class="panel-heading">
                    <div class="panel-title">
                        <?= rex_i18n::msg('multinewsletter_bounce_statistics') ?>
                    </div>
                </header>
                <div class="panel-body">
                    <?php
                    try {
                        $bounceHandler = new BounceHandler();
                        $stats = $bounceHandler->getBounceStatistics(30);
                    ?>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="rex-tile">
                                    <div class="rex-tile-number"><?= $stats['total'] ?></div>
                                    <div class="rex-tile-text"><?= rex_i18n::msg('multinewsletter_total_bounces_30_days') ?></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="rex-tile">
                                    <div class="rex-tile-number"><?= $stats['hard_bounces'] ?></div>
                                    <div class="rex-tile-text"><?= rex_i18n::msg('multinewsletter_hard_bounces') ?></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="rex-tile">
                                    <div class="rex-tile-number"><?= $stats['soft_bounces'] ?></div>
                                    <div class="rex-tile-text"><?= rex_i18n::msg('multinewsletter_soft_bounces') ?></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="rex-tile">
                                    <div class="rex-tile-number"><?= $stats['spam_complaints'] ?></div>
                                    <div class="rex-tile-text"><?= rex_i18n::msg('multinewsletter_spam_complaints') ?></div>
                                </div>
                            </div>
                        </div>
                    <?php
                    } catch (Exception $e) {
                        echo '<div class="alert alert-warning">' . rex_i18n::msg('multinewsletter_bounce_stats_error') . '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Bounces -->
<div class="row">
    <div class="col-lg-12">
        <div class="rex-addon-output">
            <div class="panel panel-edit">
                <header class="panel-heading">
                    <div class="panel-title">
                        <?= rex_i18n::msg('multinewsletter_recent_bounces') ?>
                    </div>
                </header>
                <div class="panel-body">
                    <?php
                    try {
                        $sql = rex_sql::factory();
                        $sql->setQuery('
                            SELECT b.*, u.email, u.firstname, u.lastname 
                            FROM ' . rex::getTablePrefix() . '375_bounces b
                            LEFT JOIN ' . rex::getTablePrefix() . '375_user u ON b.user_id = u.id
                            ORDER BY b.created_at DESC 
                            LIMIT 20
                        ');
                        
                        if ($sql->getRows() > 0) {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-striped">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>' . rex_i18n::msg('multinewsletter_bounce_date') . '</th>';
                            echo '<th>' . rex_i18n::msg('multinewsletter_bounce_user') . '</th>';
                            echo '<th>' . rex_i18n::msg('multinewsletter_bounce_type') . '</th>';
                            echo '<th>' . rex_i18n::msg('multinewsletter_bounce_subject') . '</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            
                            for ($i = 0; $i < $sql->getRows(); $i++) {
                                $bounce_type = $sql->getValue('bounce_type');
                                $user_name = trim($sql->getValue('firstname') . ' ' . $sql->getValue('lastname'));
                                
                                echo '<tr>';
                                echo '<td>' . date('d.m.Y H:i', strtotime($sql->getValue('created_at'))) . '</td>';
                                echo '<td>' . htmlspecialchars($user_name ?: $sql->getValue('email')) . '<br><small>' . htmlspecialchars($sql->getValue('email')) . '</small></td>';
                                echo '<td>';
                                switch ($bounce_type) {
                                    case 'hard_bounces':
                                        echo '<span class="label label-danger">' . rex_i18n::msg('multinewsletter_hard_bounce') . '</span>';
                                        break;
                                    case 'soft_bounces':
                                        echo '<span class="label label-warning">' . rex_i18n::msg('multinewsletter_soft_bounce') . '</span>';
                                        break;
                                    case 'spam_complaints':
                                        echo '<span class="label label-default">' . rex_i18n::msg('multinewsletter_spam_complaint') . '</span>';
                                        break;
                                }
                                echo '</td>';
                                echo '<td>' . htmlspecialchars($sql->getValue('subject')) . '</td>';
                                echo '</tr>';
                                
                                $sql->next();
                            }
                            
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                        } else {
                            echo '<div class="alert alert-info">' . rex_i18n::msg('multinewsletter_no_bounces_yet') . '</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="alert alert-warning">' . rex_i18n::msg('multinewsletter_bounce_list_error') . '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
