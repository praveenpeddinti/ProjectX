import {Component,OnInit,Input} from '@angular/core';
import {ActivatedRoute,Router,NavigationEnd,Params,PRIMARY_OUTLET,UrlTree,UrlSegment,UrlSegmentGroup} from '@angular/router';
import {MenuItem} from 'primeng/primeng';
import { GlobalVariable } from '../../config';
import {SharedService} from '../../services/shared.service'; //this service updates the component variable from another component

declare var jQuery:any;
@Component({
  selector: 'breadcrumb',
  templateUrl:'./breadcrumb.component.html',
  styleUrls: ['./breadcrumb.component.css'],
})

export class BreadcrumbComponent implements OnInit {
  
  private items:any=[];
  private route_url:string;
  private route_params:string;
  private path='';
  private id:any=[];
  private route_changes;
  private count=0;
  private status=false;
  private isLoggedOut=false;
  private project;
  constructor(private router:Router,private route:ActivatedRoute,private shared:SharedService){
    this.shared=shared;
    this.isLoggedOut=true;
  }
  ngOnInit()
  {
    
    var path_url;
      this.shared.getEmittedValue().subscribe(value=>
      {
        
        this.route_changes=value; //params from URL
     
        this.project=this.route_changes.projectName;
        if(this.route_changes.page!='Logout')
        {
          if(this.items.length>0 && this.count==0 && this.route_changes.page!='Detail' && this.route_changes.type!='Other')
                {
                   for (var key in this.items[0]) 
                   {
                    this.items[0].url="/project/"+this.route_changes.params+"/list";
                    this.items[0].label=this.route_changes.params;
                   }
                }
                if(this.route_changes.type=="New" && this.items.length>0)
                    {
                      this.items.push({label:this.route_changes.type,url:"/project/"+this.route_changes.params+"/new",queryString:''});
                    }
          if(this.route_changes.projectName!=''  && this.status!=true && this.project!='') //newly changed
            {
          
             if(this.id.length==1 && this.items.length==1) //newly added for The Notification Use Case
             {
               this.removeItems(0,false);
             }
                 if(this.route_changes.projectName!=undefined)
                 { 
                  this.items.push({label:this.route_changes.projectName,url:"/project/"+this.route_changes.projectName+"/list",queryString:''});
                
                this.status=true;
                this.project=this.route_changes.projectName;
                 }
              }
                 
            if((this.route_changes.page=='Detail') && !(this.id.indexOf('#'+this.route_changes.params)>-1))
            { 
          
                   if( this.items[0].type != "Other"){

                    this.items[0].url="/project/"+this.route_changes.projectName+"/list";
                    this.items[0].label=this.route_changes.projectName;
                    this.items[0].queryString='';
                    }
                    if(this.project!=this.route_changes.projectName && this.project!='')
                    {
                      this.items.splice(1,this.items.length);
                      this.project='';
                      this.status=false;
                    }
              this.items.push({label:'#'+this.route_changes.params,url:"/"+this.route_changes.url,type:this.route_changes.type,queryString:''});
              this.id.push('#'+this.route_changes.params);
              this.count++;
                       }

            /*For Back Logic */
            for (var key in this.items) 
                   {
                     if((this.items[key].label==this.route_changes.params) || (this.items[key].label=='#'+this.route_changes.params || this.route_changes.params==''))
                     {
                       for(var i:any=parseInt(key);i<this.items.length;i++)
                        {
                          if(this.route_changes.params=='')
                          {
                            this.removeItems(0,false);
                          }
                          else
                          {
                            this.items.splice(i+1,this.items.length);
                            this.id.splice(i,this.items.length);
                          }
                        }
                     }                 
                   }
                   for(var key in this.items)
                   {
                      if(this.route_changes.type=="New" && this.items.length>0)
                      {
                        if(this.items[key].label!=this.route_changes.type)
                        {
                          this.items.push({label:this.route_changes.type,url:"/project/"+this.route_changes.params+"/new",queryString:''});
                        }
                      }
                   }
            /*Back Logic End */
            
       //for search  and notifications
          if(this.route_changes.type=='Other'){ 
              this.status=true;
              if(this.id.length!=0)
              {      
                this.removeItems(1,true);
                var url = this.route_changes.url;
                var urlPart = url.split("?")[0];
                var query_string=btoa(this.route_changes.params);
                this.items.push({label:this.route_changes.page,type:this.route_changes.type,url:"/"+urlPart,queryString:query_string});
                
              }
              else
              { 
              var url = this.route_changes.url;
              var urlPart = url.split("?")[0];
               var query_string=btoa(this.route_changes.params);
                this.items.push({label:this.route_changes.page,type:this.route_changes.type,url:"/"+urlPart,queryString:query_string});
               
                if(this.items.length>1)
                {
                  for(var key in this.items) 
                    {
                      var i:any=parseInt(key);
                      if(i<this.items.length)
                      {
                        if(this.items[i].label==this.route_changes.page)
                        {
                          this.removeItems(i+1,true);
                        }
                      }
                    }
                }
               
              }
          }
          if(this.route_changes.page=='Home')
          {
             this.status=true;        
             this.removeItems(0,false);
          }
          if(this.route_changes.navigatedFrom=="Notification") //newly added by Ryan
          {
            for (var key in this.items)
            {
           
              this.removeItems(0,false);
              this.items.push({label:'#'+this.route_changes.params,url:"/"+this.route_changes.url,type:this.route_changes.type,queryString:''});
              this.id.push('#'+this.route_changes.params);
            }
          }
          this.isLoggedOut=false;
        }
        else{
              this.id=[];
              this.count=0;
              this.status=false;
              this.isLoggedOut=true;
              this.items=[];
            }
         
      });


  }
  /**
   *@description Will get call when we are selecting the previous breadcrumb from current.
*/
  modifyBreadcrumb(index)
  {
    if(index==0)
    {
      this.removeItems(index+1,true);
     
    }
    else if(index==null)
    {
      this.removeItems(0,false);
    }
    else 
    {
     this.removeItems(index+1,true); //newly added
    }
  }

/**
 * @description To remove the current breadcrumb when click on previous breadcrumb
*/
  removeItems(index,status)
  {
    for(var i=index;i<this.items.length;i++)
      {
        this.items.splice(i,this.items.length);
      }
     
        this.id=[];
        this.count=0;
        this.status=status;
     
  }

/**
 * @description To prepare pattern as we required. 
 */
getUrlVars(url)
{
    var vars = [], hash;
    var hashes = url.slice(url.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}
}