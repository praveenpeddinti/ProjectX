import { Component } from '@angular/core';
import { NavController, NavParams, ViewController, AlertController } from 'ionic-angular';
import { Storage } from "@ionic/storage";
import { LoginPage } from '../login/login';
import { Globalservice } from '../../providers/globalservice';
import {Constants} from '../../providers/constants';
import {App} from 'ionic-angular';
/*
  Generated class for the Popover page.

  See http://ionicframework.com/docs/v2/components/#navigation for more info on
  Ionic pages and navigation.
*/
@Component({
  selector: 'page-logout',
  templateUrl: 'logout.html',
  providers: [Globalservice, Constants]
})
export class LogoutPage {
  userName: string='';
  login: {username?: string, password?: string,token?:any} = {};
  logoutParams = {"userInfo":{"Id":"","username":"","token":""},"projectId":1}
  constructor(protected app: App,
            private globalService: Globalservice,
            private constants: Constants,
            public navCtrl: NavController,
            public alertController: AlertController,
            private storage:Storage,
            public navParams: NavParams, 
            public viewCtrl: ViewController) {
              this.storage.get('userCredentials').then((value) => {
                this.logoutParams.userInfo.Id= value.Id;
                this.logoutParams.userInfo.username=value.username;
                this.logoutParams.userInfo.token=value.token;
              });
            }
  public close() {
    this.viewCtrl.dismiss();
  }
  ionViewDidLoad() {
    this.userName = this.navParams.get("username");
  }
  public logoutApp() {
    this.close();
    this.globalService.getLogout(this.constants.LogutUrl,this.logoutParams).subscribe(
        data =>{
             this.storage.remove('userCredentials');  
                 this.storage.clear().then (()=>{
                    this.app.getRootNav().setRoot(LoginPage); 
                 });
            },
        error=>{ console.log("the error " + JSON.stringify(error));},
        ()=> console.log('logout api call complete'));
  }

}