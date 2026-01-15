import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import UserControls from 'flarum/forum/utils/UserControls';
import Button from 'flarum/common/components/Button';
import RedeemModal from './components/RedeemModal';

app.initializers.add('hertz-dev-redemption-code', () => {
  extend(UserControls, 'userControls', function(items, user) {
    // 只有登录用户查看自己的资料时才显示
    if (!app.session.user || app.session.user !== user) return;

    items.add('redeem', Button.component({
      icon: 'fas fa-ticket-alt',
      onclick: () => app.modal.show(RedeemModal)
    }, '使用兑换码'));
  });
});