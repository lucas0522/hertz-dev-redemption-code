import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import Stream from 'flarum/common/utils/Stream';
import app from 'flarum/forum/app';

export default class RedeemModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);
    this.code = Stream('');
  }

  className() {
    return 'RedeemModal Modal--small';
  }

  title() {
    return '使用兑换码';
  }

  content() {
    return (
      <div className="Modal-body">
        <div className="Form-group">
          <label>请输入您的兑换码</label>
          <input
            className="FormControl"
            placeholder="例如: VIP-XXXX-XXXX"
            value={this.code()}
            oninput={e => this.code(e.target.value)}
          />
        </div>
        <div className="Form-group">
          {Button.component({
            type: 'submit',
            className: 'Button Button--primary',
            loading: this.loading,
            disabled: !this.code()
          }, '立即兑换')}
        </div>
      </div>
    );
  }

  onsubmit(e) {
    e.preventDefault();
    this.loading = true;

    app.request({
      method: 'POST',
      url: app.forum.attribute('apiUrl') + '/redemption/redeem',
      body: { code: this.code() }
    }).then(() => {
      this.hide();
      app.alerts.show({ type: 'success' }, '兑换成功！您的有效期已延长。');
      window.location.reload();
    }).catch((e) => {
      this.loading = false;
      m.redraw();
    });
  }
}