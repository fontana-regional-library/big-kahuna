<table align="center" class="container body-drip float-center">
  <tbody>
    <tr>
      <td>
        <table class="spacer">
          <tbody>
            <tr>
              <td height="16px" style="font-size:16px;line-height:16px;">&#xA0;</td>
            </tr>
          </tbody>
        </table>
        <?php if(!empty($this->image)){?> <center data-parsed=""> 
        <?php echo $this->image; ?></center> <?php } ?>
        <table class="spacer">
          <tbody>
            <tr>
              <td height="16px" style="font-size:16px;line-height:16px;">&#xA0;</td>
            </tr>
          </tbody>
        </table>
        <table class="row">
          <tbody>
            <tr>
              <th class="small-12 large-12 columns first last">
                <table>
                  <tr>
                    <th>
                      <p class="text-center lead"><?php echo $this->subject; ?></p>
                      <p class="text-center subheader"><?php echo $this->tagline; ?></p>
                    </th>
                    <th class="expander"></th>
                  </tr>
                </table>
              </th>
            </tr>
          </tbody>
        </table>
        <hr>
        <table class="row">
          <tbody>
            <tr>
              <th class="small-12 large-12 columns first last">
                <table>
                  <tr>
                    <th>
                        <?php foreach($this->paragraphs as $paragraph): ?>
                        <p>
                          <?php echo $paragraph; ?>
                        </p>
                      <?php endforeach;?>
                      <?php if(!empty($this->link)){?>
                        <table class="spacer">
                        <tbody>
                          <tr>
                            <td height="16px" style="font-size:16px;line-height:16px;">&#xA0;</td>
                          </tr>
                        </tbody>
                      </table>
                      <center data-parsed="">
                        <table class="button expand">
                          <tr>
                            <td>
                              <table>
                                <tr>
                                  <td><a href="<?php echo $this->link; ?>"><?php echo $this->link_text; ?> &rarrtl;</a></td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                      </center><?php } ?>
                      <?php if(!empty($this->list)){?>
                      <table class="spacer">
                        <tbody>
                          <tr>
                            <td height="16px" style="font-size:16px;line-height:16px;">&#xA0;</td>
                          </tr>
                        </tbody>
                      </table>
                      <table class="callout">
                        <tbody>
                          <tr>
                            <th class="callout-inner <?php echo $this->class; ?>">
                            <?php echo $this->list; ?>                                
                            </th>
                            <th class="expander"></th>
                          </tr>
                        </tbody>
                      </table><?php } ?>
                    </th>
                    <th class="expander"></th>
                  </tr>
                </table>
              </th>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </tbody>
</table>