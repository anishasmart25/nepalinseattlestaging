<?php
/**
 * Result Count
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="rtcl-result-count">
    <h2>
        <?php
            if ( 1 === $total ) {
                _e( 'Showing the single result', 'listygo' );
            } elseif ( $total <= $per_page || -1 === $per_page ) {
                /* translators: %d: total results */
                printf( _n( 'Showing all %d result', 'Showing all %d results', $total, 'listygo' ), $total );
            } else {
                $first = ( $per_page * $current ) - $per_page + 1;
                $last  = min( $total, $per_page * $current );
                /* translators: 1: first result 2: last result 3: total results */
                printf( _nx( 'Showing %1$d&ndash;%2$d of %3$d result', 'Showing %1$d&ndash;%2$d of %3$d results', $total, 'with first and last result', 'listygo' ), $first, $last, $total );
            }
        ?>
    </h2>
</div>
