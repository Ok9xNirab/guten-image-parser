<?php
/**
 * @var string $generated_code Generated Code.
 */
?>
<div class="wrap">
    <h1>Gutenberg Image Parser</h1>
    <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
        <input type="hidden" name="action" value="guten_img_form">
		<?php wp_nonce_field( 'guten_img_form_nonce' ); ?>

        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="guten_img_code">Your Code</label></th>
                <td>
                    <textarea id="guten_img_code" name="guten_img_code" cols="80" rows="10"
                              class="large-text" required></textarea>
                    <br>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="guten_img_gen_code">Generated Code</label></th>
                <td>
                    <textarea id="guten_img_gen_code" name="guten_img_gen_code" cols="80" rows="10"
                              class="large-text" disabled><?php echo wp_kses_post( $generated_code ) ?></textarea>
                    <br>
                </td>
            </tr>
            </tbody>
        </table>
		<?php submit_button( 'Generate' ); ?>
    </form>
</div>