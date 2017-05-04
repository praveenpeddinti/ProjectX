import { Component,OnInit,HostListener } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { GlobalVariable } from '../../config';
import {AuthGuard} from '../../services/auth-guard.service';
import { AjaxService } from '../../ajax/ajax.service';
declare var jQuery:any;
@Component({
   selector: 'all-notification',
    templateUrl: 'notification-component.html',
     styleUrls: ['./notification-component.css'],
    providers: [AuthGuard]
})
export class NotificationComponent implements OnInit{
    public searchString="";
    public allNotification=[];
    public notify_count:any=0;
    public stringPosition;
    public pageNo=1;
    private page=1;
    public nomorenotifications:boolean= false;
    public ready=true;
     constructor(
        private _router: Router,
         private _authGuard:AuthGuard,
        private route: ActivatedRoute,
        private _ajaxService: AjaxService
        ) {

         }

    ngOnInit(){
      this.getAllNotification(this.pageNo);
  
    }

    getAllNotification(page){
    var post_data={viewAll:1,page:page};
    this._ajaxService.NodeSubscribe('/getAllNotifications',post_data,(data)=>
      {
      console.log("--leing-----viewAllNotifications--"+data.notify_result.length);
      if(data.notify_result.length >0){
          this.nomorenotifications = false;
          this.notify_count=data.notify_result.length;
           if(this.pageNo==1){
              this.allNotification=[];
       } 
       for(var i=0;i<data.notify_result.length;i++)
        {
         
            this.allNotification.push(data.notify_result[i]);
          
        }
        }else if(this.pageNo >1){
          this.nomorenotifications = true;
        }
     
      });
    }

    deleteNotification(notify_id,event) 
  {
    jQuery("#notifications_list").hide();
    event.stopPropagation();
    //ajax call for delete notificatin
    var post_data={'notifyid':notify_id,viewAll:1,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
          this.notify_count=data.totalCount;
       if(data.data.notify_result != "nodata"){
        jQuery('#mark_'+notify_id).remove(); 
        jQuery('#notify_no_'+notify_id).removeClass('unreadnotification'); 
       }
       
      }
    })


  }

  goToTicket(ticketid,notify_id)
  {
    var post_data={'notifyid':notify_id,viewAll:1,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
       // do something
      jQuery('#mark_'+notify_id).remove(); 
      jQuery('#notify_no_'+notify_id).removeClass('unreadnotification'); 
      }
      this._router.navigate(['story-detail',ticketid]);
    })
    
  }
  goToComment(ticketid,comment,notify_id)
  {
    var post_data={'notifyid':notify_id,viewAll:1,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
        jQuery('#mark_'+notify_id).remove(); 
        jQuery('#notify_no_'+notify_id).removeClass('unreadnotification'); 
      }
    })
    this._router.navigate(['story-detail',ticketid],{queryParams: {Slug:comment}});
  }
  markAllRead()
  {
    var post_data={};
    this._ajaxService.AjaxSubscribe('story/delete-notifications',post_data,(data)=>
    {
      if(data)
      {
        this.notify_count=0;
        
       jQuery(".notificationdelete").remove();
       jQuery('.notificationdiv').removeClass('unreadnotification'); 
        // jQuery("#notificationMessage").show();
       
       
      }
    })
  }

 @HostListener('window:scroll', ['$event']) 
    doSomething(event) {
     // console.debug("Scroll Event", window.pageYOffset );
      if (this.allNotification.length > 0 && jQuery(window).scrollTop() == jQuery(document).height() - jQuery(window).height()) {

          this.pageNo +=1; 
          this.getAllNotification(this.pageNo)     
      }
    }

  
}