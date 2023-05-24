import {  useEffect, useState } from 'react'

import axios from 'axios';
import { nanoid } from 'nanoid';
import { CustomizedTreeView, StyledTreeItem } from './TreeView';
import React from 'react';

const API_URL = 'http://127.0.0.1:8000/api/';

export const Btree = (props: any) => {
  const [loaded, setLoaded] = useState<boolean>(false);
  const [btreeData, setBtreeData] = useState<any>([])

  useEffect(() => {
    fetchBtreeData().then((response)=> {
      setBtreeData(response.data);
      setLoaded(true);
      }).catch((err) => console.log(err))
  }, [])

  const fetchBtreeData = () => {
    return axios.get(API_URL + 'btree')
  }

  if (!loaded) return null;
 


 const RecursiveComponent = ({ data, parentGuid }: any) => {
  
  const activeNodeData = data.filter((element: any) => element.parentguid == parentGuid)
  return (
    <React.Fragment key={nanoid(11)}>
      {Object.entries(activeNodeData).map(([_, item]: any) => {
      
         return (
          <React.Fragment key={nanoid(11)}>
            {typeof(activeNodeData) !== 'undefined' && item.hasChildren > 0 ?
            (
            <StyledTreeItem key={nanoid(11)} nodeId={item.guid} label={item.name} >
              { <RecursiveComponent key={nanoid(11)} data={ data} parentGuid={item.guid}/>}
              </StyledTreeItem>
              ) : (<StyledTreeItem key={nanoid(11)} nodeId={item.guid + '_' + nanoid(3)} label={item.name} />)
            }
          </React.Fragment>
        );
      })}
      </React.Fragment>
  );
};

  return (<div style={props.style}><CustomizedTreeView key={nanoid(11)} children={RecursiveComponent({data: btreeData, parentGuid: null})}></CustomizedTreeView></div>);
}
