import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Button from 'flarum/common/components/Button';

app.initializers.add('hertz-dev-redemption-code', () => {
  app.extensionData.for('hertz-dev-redemption-code').registerPage(RedemptionPage);
});

class RedemptionPage extends ExtensionPage {
  oninit(vnode) {
    super.oninit(vnode);
    
    this.codes = [];
    this.loading = false;
    
    // 生成用的表单数据
    this.newGroup = 10; // 默认群组ID，可修改
    this.newDays = 30;
    this.newAmount = 1;

    this.refresh();
  }

  refresh() {
    this.loading = true;
    app.request({
      method: 'GET',
      url: app.forum.attribute('apiUrl') + '/redemption-codes'
    }).then(response => {
      this.codes = response.data;
      this.loading = false;
      m.redraw();
    });
  }

  generate() {
    this.loading = true;
    app.request({
      method: 'POST',
      url: app.forum.attribute('apiUrl') + '/redemption-codes',
      body: {
        data: {
          attributes: {
            groupId: this.newGroup,
            days: this.newDays,
            amount: this.newAmount
          }
        }
      }
    }).then(() => {
      // 生成成功后刷新列表
      this.refresh();
      app.alerts.show({ type: 'success' }, '生成成功！');
    });
  }

  content() {
    return (
      <div className="ExtensionPage-settings">
        <div className="container">
          <h2>兑换码管理</h2>
          
          {/* 生成区 */}
          <div className="Form-group" style="background: #f0f0f0; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <h3>生成新码</h3>
            <div style="display: flex; gap: 10px; align-items: flex-end;">
                <div>
                    <label>群组 ID</label>
                    <input className="FormControl" type="number" value={this.newGroup} oninput={e => this.newGroup = e.target.value} />
                </div>
                <div>
                    <label>天数</label>
                    <input className="FormControl" type="number" value={this.newDays} oninput={e => this.newDays = e.target.value} />
                </div>
                <div>
                    <label>数量</label>
                    <input className="FormControl" type="number" value={this.newAmount} oninput={e => this.newAmount = e.target.value} />
                </div>
                <Button className="Button Button--primary" onclick={this.generate.bind(this)} loading={this.loading}>
                    开始生成
                </Button>
            </div>
          </div>

          {/* 列表区 */}
          <table style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #ddd;">
                    <th style="padding: 10px;">兑换码</th>
                    <th style="padding: 10px;">内容</th>
                    <th style="padding: 10px;">状态</th>
                    <th style="padding: 10px;">创建时间</th>
                </tr>
            </thead>
            <tbody>
                {this.codes.map(code => {
                    const attr = code.attributes;
                    const payload = JSON.parse(attr.payload);
                    return (
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px; font-family: monospace; font-weight: bold;">{attr.code}</td>
                            <td style="padding: 10px;">群组 {payload.groupId} / {payload.days} 天</td>
                            <td style="padding: 10px;">
                                {attr.isUsed ? 
                                    <span style="color: red;">已使用</span> : 
                                    <span style="color: green;">未使用</span>
                                }
                            </td>
                            <td style="padding: 10px;">{attr.createdAt.split('T')[0]}</td>
                        </tr>
                    );
                })}
            </tbody>
          </table>
        </div>
      </div>
    );
  }
}